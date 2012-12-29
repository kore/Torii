<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\MimeTypeGuesser;

use Torii\Assets\MimeTypeGuesser;
use Torii\Assets\Struct\File;

/**
 * Mime type guesser
 *
 * @version $Revision$
 */
class Extension extends MimeTypeGuesser
{
    /**
     * Mapping of file extensions to mime types.
     *
     * A list may be found at, and can be extended further from:
     * http://www.w3schools.com/media/media_mimeref.asp
     *
     * @var array
     */
    protected $extensionMapping = array(
        // Application specific extensions
        '.pdf'     => 'application/pdf',
        '.sig'     => 'application/pgp-signature',
        '.ps'      => 'application/postscript',
        '.rtf'     => 'application/rtf',
        '.swf'     => 'application/x-shockwave-flash',

        // Archives
        '.gz'      => 'application/x-gzip',
        '.tar.gz'  => 'application/x-tgz',
        '.tgz'     => 'application/x-tgz',
        '.tar'     => 'application/x-tar',
        '.zip'     => 'application/zip',
        '.bz2'     => 'application/x-bzip',
        '.tbz'     => 'application/x-bzip-compressed-tar',
        '.tar.bz2' => 'application/x-bzip-compressed-tar',

        // Audio formats
        '.mp3'     => 'audio/mpeg',
        '.m3u'     => 'audio/x-mpegurl',
        '.wma'     => 'audio/x-ms-wma',
        '.wax'     => 'audio/x-ms-wax',
        '.ogg'     => 'application/ogg',
        '.wav'     => 'audio/x-wav',

        // Video formats
        '.mpeg'    => 'video/mpeg',
        '.mpg'     => 'video/mpeg',
        '.mov'     => 'video/quicktime',
        '.qt'      => 'video/quicktime',
        '.avi'     => 'video/x-msvideo',
        '.asf'     => 'video/x-ms-asf',
        '.asx'     => 'video/x-ms-asf',
        '.wmv'     => 'video/x-ms-wmv',

        // Image formats
        '.gif'     => 'image/gif',
        '.jpg'     => 'image/jpeg',
        '.jpeg'    => 'image/jpeg',
        '.png'     => 'image/png',
        '.xbm'     => 'image/x-xbitmap',
        '.xpm'     => 'image/x-xpixmap',
        '.xwd'     => 'image/x-xwindowdump',
        '.bmp'     => 'image/bmp',
        '.tif'     => 'image/tiff',
        '.tiff'    => 'image/tiff',
        '.ico'     => 'image/x-icon',
        '.svgz'    => 'image/svg+xml',
        '.svg'     => 'image/svg+xml',

        // Plain text and code
        '.min.css' => 'text/css',
        '.css'     => 'text/css',
        '.html'    => 'text/html',
        '.htm'     => 'text/html',
        '.js'      => 'text/javascript',
        '.min.js'  => 'text/javascript',
        '.asc'     => 'text/plain',
        '.c'       => 'text/plain',
        '.h'       => 'text/plain',
        '.cc'      => 'text/plain',
        '.cpp'     => 'text/plain',
        '.hh'      => 'text/plain',
        '.hpp'     => 'text/plain',
        '.conf'    => 'text/plain',
        '.log'     => 'text/plain',
        '.text'    => 'text/plain',
        '.txt'     => 'text/plain',
        '.diff'    => 'text/plain',
        '.patch'   => 'text/plain',
        '.php'     => 'text/plain',
        '.ini'     => 'text/plain',
        '.dtd'     => 'text/xml',
        '.xml'     => 'text/xml',
        '.mustache' => 'text/mustache',
    );

    /**
     * Guess mime type for given file
     *
     * @param File $file
     * @return string
     */
    public function guess(File $file)
    {
        if (!preg_match('((?:\\.[a-zA-Z0-9]+)+$)', $file->localPath, $match)) {
            // The file does not have any extension.
            return 'application/octet-stream';
        }

        $extension = strtolower($match[0]);
        if (!isset($this->extensionMapping[$extension])) {
            // We do not know about this extension yet.
            return 'application/octet-stream';
        }

        return $this->extensionMapping[$extension];
    }
}
