<?php

/**
 * Created
 * User: alan
 * Date: 03/10/17
 * Time: 10:10
 */
/**
 *
 * Needs to run from server cron as job initialisation can take a very long time
 *
 */
namespace Fullworks_Preprocess_Dezrez\Includes;

use  DateTime ;
use  DateTimeZone ;
class Download_Dezrez
{
    protected  $cache_dir ;
    protected  $result_dir ;
    protected  $now ;
    protected  $last_run ;
    protected  $api_search_url ;
    protected  $api_single_url ;
    protected  $api_image_url ;
    protected  $api_args ;
    protected  $prop_list ;
    protected  $prop_update_count ;
    protected  $img_update_count ;
    protected  $retry_wait_sec ;
    protected  $retry_attempts ;
    public function __construct( $freemiusSDK )
    {
        $this->freemiusSDK = $freemiusSDK;
        $this->set_directories();
    }
    
    private function set_directories()
    {
        $upload_dir = wp_upload_dir();
        // for work files
        $this->result_dir = $upload_dir['basedir'] . '/preprocess-dezrez/';
        if ( !file_exists( $this->result_dir ) ) {
            wp_mkdir_p( $this->result_dir );
        }
        $this->cache_dir = $upload_dir['basedir'] . '/preprocess-dezrez/cache';
        if ( !file_exists( $this->cache_dir ) ) {
            wp_mkdir_p( $this->cache_dir );
        }
    }
    
    public static function last_run()
    {
        $date = get_transient( 'fpd-process-last-run' );
        if ( $date ) {
            return $date['last']->format( 'Y-m-d H:i:s' );
        }
        return __( 'never run' );
    }
    
    public function run()
    {
        $this->prop_update_count = 0;
        $this->img_update_count = 0;
        $this->retry_wait_sec = 3;
        $this->retry_attempts = 10;
        $this->set_up();
        $this->process_properties();
        $this->finalise_output();
    }
    
    private function set_up()
    {
        $check_all_import = get_transient( 'fpd_all_import_running' );
        
        if ( !empty($check_all_import) ) {
            Core::log_it( esc_html__( 'Looks like All Import is running something so die for now', 'preprocess-dezrez' ) );
            die;
        }
        
        $this->api_search_url = "http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/Default.aspx";
        $this->api_args = array(
            'perpage' => '9999',
            'showSTC' => 'true',
            'apikey'  => $this->get_setting( 'api', 'key' ),
            'eaid'    => $this->get_setting( 'api', 'eaid' ),
            'xslt'    => -1,
        );
        $this->api_single_url = 'http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/Property.aspx';
        $this->api_image_url = 'http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/pictureResizer.aspx';
        $this->prop_list = array();
        $this->get_set_runtime();
    }
    
    public function get_setting( $section_id, $field_id )
    {
        $options = get_option( 'Fullworks_Preprocess_Dezrez_settings' );
        if ( isset( $options[$section_id . '_' . $field_id] ) ) {
            return $options[$section_id . '_' . $field_id];
        }
        return false;
    }
    
    private function get_set_runtime()
    {
        // transients for run time data
        $this->last_run = get_transient( 'fpd-process-last-run' );
        // if transient not set set an old date
        if ( empty($this->last_run) ) {
            $this->last_run = array(
                'last'         => new DateTime( '2000-01-01' ),
                'last_trigger' => new DateTime( '2000-01-01' ),
                'prev'         => new DateTime( '2000-01-01' ),
            );
        }
        $this->now = new DateTime( "now", new DateTimeZone( 'Europe/London' ) );
        Core::log_it( array(
            'msg'        => 'Times at start',
            'last run'   => $this->last_run['last']->format( 'Y-m-d H:i:s' ),
            'start time' => $this->now->format( 'Y-m-d H:i:s' ),
        ) );
    }
    
    private function process_properties()
    {
        $sales = $this->get_properties_list( 'propertySearchSales' );
        $rentals = $this->get_properties_list( 'propertySearchLettings' );
        $this->prop_list = array_merge( $sales, $rentals );
        Core::log_it( array(
            'msg'     => esc_html__( 'Property counts', 'preprocess-dezrez' ),
            'sales'   => Count( $sales ),
            'rentals' => Count( $rentals ),
            'total'   => Count( $this->prop_list ),
        ) );
        
        if ( Count( $this->prop_list ) == 0 ) {
            Core::log_it( esc_html__( 'No properties nothing to do or a big problem so die', 'preprocess-dezrez' ) );
            die;
        }
        
        // download xml only for updated
        unset( $this->api_args['perpage'], $this->api_args['showSTC'], $this->api_args['rentalPeriod'] );
        foreach ( $this->prop_list as $prop ) {
            
            if ( $prop['updated'] === true ) {
                $xml = $this->get_property_single( $prop['id'] );
                $this->prop_update_count++;
            }
        
        }
    }
    
    private function get_properties_list( $search )
    {
        $list = array();
        if ( $search == 'propertySearchLettings' ) {
            $this->api_args['rentalPeriod'] = '6';
        }
        $url = add_query_arg( $this->api_args, $this->api_search_url );
        $xml = $this->api_call( $url );
        foreach ( $xml->{$search}->properties->property as $prop ) {
            $date = DateTime::createFromFormat( 'j/n/Y G:i:s', $this->xml_attribute( $prop, 'updated' ), new DateTimeZone( 'Europe/London' ) );
            $list[] = array(
                'id'      => $this->xml_attribute( $prop, 'id' ),
                'updated' => $date > $this->last_run['last'],
            );
        }
        return $list;
    }
    
    private function api_call( $url )
    {
        $response = wp_remote_get( $url );
        if ( is_wp_error( $response ) ) {
            die( sprintf( esc_html__( 'Failed getting %1$s %2$s', 'preprocess-dezrez' ), $url, print_r( $response, true ) ) );
        }
        $body = wp_remote_retrieve_body( $response );
        return simplexml_load_string( $body );
    }
    
    private function xml_attribute( $object, $attribute )
    {
        if ( isset( $object[$attribute] ) ) {
            return (string) $object[$attribute];
        }
        if ( $attribute == "updated" ) {
            return "1/1/2999 13:00:00";
        }
        return "";
    }
    
    private function get_property_single( $id )
    {
        $this->api_args['pid'] = $id;
        $url = add_query_arg( $this->api_args, $this->api_single_url );
        $xml = $this->api_call( $url );
        $i = 0;
        $xml->propertyFullDetails->property->asXML( $this->cache_dir . '/dezrez-' . $id . '.xml' );
        return $xml;
    }
    
    private function get_property_image( $picture, $id, $picnum )
    {
        $image_url = (string) $picture;
        // Need to require these files
        // from codex https://codex.wordpress.org/Function_Reference/media_handle_sideload
        
        if ( !function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . "wp-admin" . '/includes/image.php';
            require_once ABSPATH . "wp-admin" . '/includes/file.php';
            require_once ABSPATH . "wp-admin" . '/includes/media.php';
        }
        
        $image_url = $this->add_image_width( $image_url );
        $tmp = $this->download_url( $image_url );
        if ( is_wp_error( $tmp ) ) {
            die( "Failed getting " . $image_url . " " . print_r( $tmp, true ) );
        }
        $file_array = array(
            'name'     => 'dezrez-' . $id . '-' . $picnum . $this->file_ext( wp_remote_retrieve_header( $tmp[1], 'content-type' ) ),
            'tmp_name' => $tmp[0],
        );
        /* to set post content add something like
        		array(
        		'post_content'=> trim($this->xml_attribute( $picture, 'category' )))
        		*/
        $media_id = media_handle_sideload(
            $file_array,
            0,
            'dezrez-' . $id . '-' . $picnum,
            array(
            'post_content' => trim( $this->xml_attribute( $picture, 'category' ) ),
        )
        );
        // Check for handle sideload errors.
        
        if ( is_wp_error( $id ) ) {
            sleep( $this->retry_wait_sec );
            $media_id = media_handle_sideload( $file_array, 0, 'dezrez-' . $id . '-' . $picnum );
            
            if ( is_wp_error( $media_id ) ) {
                @unlink( $file_array['tmp_name'] );
                die( "Failed on single image to media file " . print_r( $id, true ) );
            }
        
        }
        
        $image_attributes = wp_get_attachment_image_src( $media_id, 'full' );
        return basename( $image_attributes[0] );
    }
    
    private function add_image_width( $image_url )
    {
        if ( strpos( $image_url, 'ImageResizeHandler' ) !== false ) {
            $image_url = add_query_arg( array(
                'width' => '1400',
            ), $image_url );
        }
        return $image_url;
    }
    
    /**
     * Downloads a URL to a local temporary file using the WordPress HTTP Class.
     * Please note, That the calling function must unlink() the file.
     *
     * @since 2.5.0
     *
     * @param string $url the URL of the file to download
     * @param int $timeout The timeout for the request to download the file default 300 seconds
     *
     * @return mixed WP_Error on failure, string Filename on success.
     *
     * alan - need to examine headers to return file type so return extended ..
     * @return Array ( mixed WP_Error on failure, string Filename on success, Array - response )
     */
    function download_url( $url, $timeout = 300 )
    {
        //WARNING: The file is not automatically deleted, The script must unlink() the file.
        if ( !$url ) {
            return array( new WP_Error( 'http_no_url', __( 'Invalid URL Provided.' ) ) );
        }
        $tmpfname = wp_tempnam( $url );
        if ( !$tmpfname ) {
            return array( new WP_Error( 'http_no_file', __( 'Could not create Temporary file.' ) ) );
        }
        $response = wp_safe_remote_get( $url, array(
            'timeout'  => $timeout,
            'stream'   => true,
            'filename' => $tmpfname,
        ) );
        if ( is_wp_error( $response ) ) {
            return array( $response );
        }
        
        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            unlink( $tmpfname );
            return array( new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) ) );
        }
        
        $content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
        
        if ( $content_md5 ) {
            $md5_check = verify_file_md5( $tmpfname, $content_md5 );
            
            if ( is_wp_error( $md5_check ) ) {
                unlink( $tmpfname );
                return array( $md5_check );
            }
        
        }
        
        return array( $tmpfname, $response );
    }
    
    private function file_ext( $contentType )
    {
        $map = array(
            'application/pdf' => '.pdf',
            'application/zip' => '.zip',
            'image/gif'       => '.gif',
            'image/jpeg'      => '.jpg',
            'image/png'       => '.png',
            'text/css'        => '.css',
            'text/html'       => '.html',
            'text/javascript' => '.js',
            'text/plain'      => '.txt',
            'text/xml'        => '.xml',
        );
        if ( isset( $map[$contentType] ) ) {
            return $map[$contentType];
        }
        $pieces = explode( '/', $contentType );
        return '.' . array_pop( $pieces );
    }
    
    private function get_property_image_name( $picture, $id, $picnum )
    {
        $image_url = (string) $picture;
        $file = $this->cache_dir . '/dezrez-' . $id . '.xml';
        // if there is a cache file - read it and that is it idf there is an image
        
        if ( file_exists( $file ) ) {
            $xml = simplexml_load_file( $file );
            if ( !empty($xml->media->picture[$picnum - 1]) ) {
                return (string) $xml->media->picture[$picnum - 1];
            }
        }
        
        // if there is no cache file we will have to regrab the image
        return $this->get_property_image( $picture, $id, $picnum );
    }
    
    private function finalise_output()
    {
        // build xml from cached file
        $files = glob( $this->cache_dir . '/*.xml' );
        $xml = simplexml_load_string( "<properties></properties>" );
        if ( $files === false ) {
            die( esc_html__( 'System Issue trying to check cache directory', 'preprocess-dezrez' ) );
        }
        
        if ( is_array( $files ) ) {
            // reconcile
            foreach ( $this->prop_list as $prop ) {
                $file = $this->cache_dir . "/dezrez-" . $prop['id'] . ".xml";
                
                if ( !is_file( $file ) ) {
                    Core::log_it( array(
                        'msg' => 'missing property',
                        'id'  => $prop['id'],
                    ) );
                    $xml = $this->get_property_single( $prop['id'] );
                }
            
            }
            // reload files after reconcilation as more may have been added
            $files = glob( $this->cache_dir . '/*.xml' );
            $delete_counter = 0;
            $load_counter = 0;
            foreach ( $files as $file ) {
                // loop
                // delete if it is not in the master list
                $id = preg_replace( '/\\D/', '', $file );
                
                if ( array_search( $id, array_column( $this->prop_list, 'id' ) ) === false ) {
                    // delete
                    $delete_counter++;
                    unlink( $file );
                    Core::log_it( array(
                        'msg'  => 'Property deleted',
                        'file' => $file,
                    ) );
                } else {
                    // add to results
                    $xmlsub = simplexml_load_file( $file );
                    $this->xml_append( $xml, $xmlsub );
                    $load_counter++;
                }
            
            }
            $xml->asXML( $this->result_dir . 'upload.xml' );
            $now = new DateTime( "now", new DateTimeZone( 'Europe/London' ) );
            Core::log_it( array(
                'msg'                     => 'End Stats',
                'end time'                => $now->format( 'Y-m-d H:i:s' ),
                'properties deleted'      => $delete_counter,
                'properties in load file' => $load_counter,
                'properties with updates' => $this->prop_update_count,
                'images with updates'     => $this->img_update_count,
            ) );
        }
        
        // set last run time at the end - but with time when run started
        $transient = array(
            'last'         => $this->now,
            'last_trigger' => $this->last_run['last_trigger'],
            'prev'         => $this->last_run['prev'],
        );
        set_transient( 'fpd-process-last-run', $transient, 1 * MONTH_IN_SECONDS );
        // having an expiry stops autoloading
    }
    
    private function xml_append( $to, $from )
    {
        $toDom = dom_import_simplexml( $to );
        $fromDom = dom_import_simplexml( $from );
        $toDom->appendChild( $toDom->ownerDocument->importNode( $fromDom, true ) );
    }
    
    private function http_headers( $url )
    {
        // DezRez dosn't like HEADER request so have to use this - or other options to speed up the redirect or the URL to image
        $headers = get_headers( $url, 1 );
        if ( false === $headers ) {
            die( sprintf( esc_html__( 'Failed getting %1$s', 'preprocess-dezrez' ), $url ) );
        }
        return $headers;
    }
    
    private function delete_property_image( $id, $picnum )
    {
        $attachment = get_page_by_title( 'dezrez-' . $id . '-' . $picnum, OBJECT, 'attachment' );
        while ( !empty($attachment) ) {
            // loop just incase dups have been created
            if ( false === wp_delete_attachment( $attachment->ID, true ) ) {
                break;
            }
            $attachment = get_page_by_title( 'dezrez-' . $id . '-' . $picnum, OBJECT, 'attachment' );
        }
    }

}