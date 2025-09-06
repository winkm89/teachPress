<?php
/**
 * This file contains all functions of the tp_icons class
 * @package teachpress\core\icons
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 7.0
 */

/**
 * This class contains some function around icon usage
 * @package teachpress\core\icons
 * @since 7.0
 */
class TP_Icons {
    
    /**
     * Returns the suitable icon classes for a given file_path or URL
     * @param string $url_link
     * @return string
     * @since 7.0
     */
    public static function get_class ($url_link) {
        $file_endings = self::files();
        $web_services = self::web();
        
        $file_end = substr($url_link,-4,4);
        preg_match( "/^(https?:\/\/)?(.+)$/", $url_link, $urltype);
        $urltype = preg_split("#/#", $urltype[2])[0];
        $urltype = explode(".", $urltype);
        if ( count($urltype)-2 > 0 ) {
            $urltype = $urltype[count($urltype)-2].".".end($urltype);
        }
        else {
            $urltype = $urltype[0].".".end($urltype);
        }
        
        // if it's a file
        if ( isset ($file_endings[$file_end]) ) {
            return $file_endings[$file_end];
        }
        // or a web service
        elseif ( isset ($web_services[$urltype]) ) {
            return $web_services[$urltype];
        }
        // default
        else {
            return 'fas fa-globe';
        }
    }

    /**
     * Returns an array with file endings and their suitable font awesome class names
     * @return string
     * @since 7.0
     */
    private static function files () {
        $file = [
            'doi'   => 'ai ai-doi',
            '.pdf'  => 'fas fa-file-pdf',
            '.doc'  => 'fas fa-file-word',
            'docx'  => 'fas fa-file-word',
            '.ppt'  => 'fas fa-file-powerpoint',
            'pptx'  => 'fas fa-file-powerpoint',
            '.xls'  => 'fas fa-file-excel',
            'xlsx'  => 'fas fa-file-excel',
            '.odt'  => 'fas fa-file-word',
            '.ods'  => 'fas fa-file-excel',
            '.odp'  => 'fas fa-file-powerpoint',
            '.odf'  => 'fas fa-file-code',
            '.odg'  => 'fas fa-file-contract',
            '.odc'  => 'fas fa-file-contract',
            '.odi'  => 'fas fa-file-contract',
            '.rtf'  => 'fas fa-file-alt',
            '.rdf'  => 'fas fa-file-alt',
            '.txt'  => 'fas fa-file-alt',
            '.tex'  => 'fas fa-file-alt',
            'html'  => 'fas fa-globe',
            'htm'   => 'fas fa-globe',
            '.php'  => 'fab fa-php',
            '.xml'  => 'fas fa-file-code',
            '.css'  => 'fas fa-file-code',
            '.py'   => 'fas fa-file-code',
            '.ipynb'=> 'fas fa-file-code',
            '.csv'  => 'fas fa-file-csv',
            '.dat'  => 'fas fa-file-alt',
            '.db'   => 'fas fa-database',
            '.dbf'  => 'fas fa-database',
            '.log'  => 'fas fa-file-alt',
            '.mdb'  => 'fas fa-database',
            '.sql'  => 'fas fa-database',
            '.sav'  => 'fas fa-file-alt',
            '.sav'  => 'fas fa-file-alt',
            '.mid'  => 'fas fa-file-audio',
            '.midi' => 'fas fa-file-audio',
            '.mp3'  => 'fas fa-file-audio',
            '.ogg'  => 'fas fa-file-audio',
            '.wma'  => 'fas fa-file-audio',
            '.wav'  => 'fas fa-file-audio',
            '.wpl'  => 'fas fa-file-audio',
            '.ai'   => 'fas fa-file-image',
            '.bmp'  => 'fas fa-file-image',
            '.gif'  => 'fas fa-file-image',
            '.ico'  => 'fas fa-file-image',
            '.jpg'  => 'fas fa-file-image',
            '.jpeg' => 'fas fa-file-image',
            '.png'  => 'fas fa-file-image',
            '.psd'  => 'fas fa-file-image',
            '.svg'  => 'fas fa-file-image',
            '.dvi'  => 'fas fa-file-image',
            '.tif'  => 'fas fa-file-image',
            '.tiff' => 'fas fa-file-image',
            '.3g2'  => 'fas fa-file-video',
            '.3gp'  => 'fas fa-file-video',
            '.avi'  => 'fas fa-file-video',
            '.flv'  => 'fas fa-file-video',
            '.h264' => 'fas fa-file-video',
            '.m4v'  => 'fas fa-file-video',
            '.mkv'  => 'fas fa-file-video',
            '.mov'  => 'fas fa-file-video',
            '.mp4'  => 'fas fa-file-video',
            '.wmv'  => 'fas fa-file-video',
            '.mpg'  => 'fas fa-file-video',
            '.mpeg' => 'fas fa-file-video',
            '.wmv'  => 'fas fa-file-video',
            '.7z'   => 'fas fa-file-archive',
            '.arj'  => 'fas fa-file-archive',   
            '.deb'  => 'fas fa-file-archive',
            '.pkg'  => 'fas fa-file-archive',
            '.rar'  => 'fas fa-file-archive',
            '.rpm'  => 'fas fa-file-archive',
            '.tar.gz' => 'fas fa-file-archive',
            '.gz'   => 'fas fa-file-archive',     
            '.zip'  => 'fas fa-file-archive',
        ];
        return $file;
    }
    
    /**
     * Returns an array with web services and their suitable font awesome class names
     * @return string
     * @since 7.0
     */
    private static function web () {
        $web = [
            'arxiv.org'         => 'ai ai-arxiv',
            'osf.io'            => 'ai ai-osf',
            'github.com'        => 'fab fa-github',
            'gitlab.com'        => 'fab fa-gitlab',
            'mendeley.com'      => 'ai ai-mendeley',
            'overleaf.com'      => 'ai ai-overleaf',
            'soundcloud.com'    => 'fab fa-soundcloud',
            'slideshare.net'    => 'fab fa-slideshare',
            'vimeo.com'         => 'fab fa-vimeo-v',
            'wikipedia.org'     => 'fab fa-wikipedia-w',
            'wordpress.com'     => 'fab fa-wordpress',
            'youtu.be'          => 'fab fa-youtube',
            'youtube.com'       => 'fab fa-youtube',
        ];
        return $web;
    }
}

