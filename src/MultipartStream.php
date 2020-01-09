<?php


namespace fize\stream;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;


class MultipartStream extends StreamDecorator implements StreamInterface
{
    private $boundary;

    /**
     * @param array $elements Array of associative arrays, each containing a
     *                         required "name" key mapping to the form field,
     *                         name, a required "contents" key mapping to a
     *                         StreamInterface/resource/string, an optional
     *                         "headers" associative array of custom headers,
     *                         and an optional "filename" key mapping to a
     *                         string to send as the filename in the part.
     * @param string $boundary You can optionally provide a specific boundary
     */
    public function __construct(array $elements = [], $boundary = null)
    {
        $this->boundary = $boundary ?: sha1(uniqid('', true));
        $this->stream = $this->createStream($elements);
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    public function isWritable()
    {
        return false;
    }

    /**
     * Get the headers needed before transferring the content of a POST file
     * @param array $headers
     * @return string
     */
    private function getHeaders(array $headers)
    {
        $str = '';
        foreach ($headers as $key => $value) {
            $str .= "{$key}: {$value}\r\n";
        }

        return "--{$this->boundary}\r\n" . trim($str) . "\r\n\r\n";
    }

    /**
     * Create the aggregate stream that will be used to upload the POST data
     * @param array $elements
     * @return StreamInterface
     */
    protected function createStream(array $elements)
    {
        $stream = new AppendStream();

        foreach ($elements as $element) {
            $this->addElement($stream, $element);
        }

        // Add the trailing boundary with CRLF
        $stream->addStream(Stream::create("--{$this->boundary}--\r\n"));

        return $stream;
    }

    private function addElement(AppendStream $stream, array $element)
    {
        foreach (['contents', 'name'] as $key) {
            if (!array_key_exists($key, $element)) {
                throw new InvalidArgumentException("A '{$key}' key is required");
            }
        }

        $element['contents'] = Stream::create($element['contents']);

        if (empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');
            if (substr($uri, 0, 6) !== 'php://') {
                $element['filename'] = $uri;
            }
        }

        list($body, $headers) = $this->createElement(
            $element['name'],
            $element['contents'],
            isset($element['filename']) ? $element['filename'] : null,
            isset($element['headers']) ? $element['headers'] : []
        );

        $stream->addStream(Stream::create($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream(Stream::create("\r\n"));
    }

    /**
     * @param $name
     * @param StreamInterface $stream
     * @param $filename
     * @param array $headers
     * @return array
     * @return array
     */
    private function createElement($name, StreamInterface $stream, $filename, array $headers)
    {
        // Set a default content-disposition header if one was no provided
        $disposition = $this->getHeader($headers, 'content-disposition');
        if (!$disposition) {
            $headers['Content-Disposition'] = ($filename === '0' || $filename)
                ? sprintf('form-data; name="%s"; filename="%s"',
                    $name,
                    basename($filename))
                : "form-data; name=\"{$name}\"";
        }

        // Set a default content-length header if one was no provided
        $length = $this->getHeader($headers, 'content-length');
        if (!$length) {
            if ($length = $stream->getSize()) {
                $headers['Content-Length'] = (string)$length;
            }
        }

        // Set a default Content-Type if one was not supplied
        $type = $this->getHeader($headers, 'content-type');
        if (!$type && ($filename === '0' || $filename)) {
            if ($type = self::mimetypeFromFilename($filename)) {
                $headers['Content-Type'] = $type;
            }
        }

        return [$stream, $headers];
    }

    private function getHeader(array $headers, $key)
    {
        $lowercaseHeader = strtolower($key);
        foreach ($headers as $k => $v) {
            if (strtolower($k) === $lowercaseHeader) {
                return $v;
            }
        }

        return null;
    }

    /**
     * Determines the mimetype of a file by looking at its extension.
     *
     * @param $filename
     *
     * @return null|string
     */
    private static function mimetypeFromFilename($filename)
    {
        return self::mimetypeFromExtension(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Maps a file extensions to a mimetype.
     *
     * @param $extension string The file extension.
     *
     * @return string|null
     * @link http://svn.apache.org/repos/asf/httpd/httpd/branches/1.3.x/conf/mime.types
     */
    private static function mimetypeFromExtension($extension)
    {
        static $mimetypes = [
            '3gp'     => 'video/3gpp',
            '7z'      => 'application/x-7z-compressed',
            'aac'     => 'audio/x-aac',
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'asf'     => 'video/x-ms-asf',
            'atom'    => 'application/atom+xml',
            'avi'     => 'video/x-msvideo',
            'bmp'     => 'image/bmp',
            'bz2'     => 'application/x-bzip2',
            'cer'     => 'application/pkix-cert',
            'crl'     => 'application/pkix-crl',
            'crt'     => 'application/x-x509-ca-cert',
            'css'     => 'text/css',
            'csv'     => 'text/csv',
            'cu'      => 'application/cu-seeme',
            'deb'     => 'application/x-debian-package',
            'doc'     => 'application/msword',
            'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dvi'     => 'application/x-dvi',
            'eot'     => 'application/vnd.ms-fontobject',
            'eps'     => 'application/postscript',
            'epub'    => 'application/epub+zip',
            'etx'     => 'text/x-setext',
            'flac'    => 'audio/flac',
            'flv'     => 'video/x-flv',
            'gif'     => 'image/gif',
            'gz'      => 'application/gzip',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'ico'     => 'image/x-icon',
            'ics'     => 'text/calendar',
            'ini'     => 'text/plain',
            'iso'     => 'application/x-iso9660-image',
            'jar'     => 'application/java-archive',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'text/javascript',
            'json'    => 'application/json',
            'latex'   => 'application/x-latex',
            'log'     => 'text/plain',
            'm4a'     => 'audio/mp4',
            'm4v'     => 'video/mp4',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mov'     => 'video/quicktime',
            'mkv'     => 'video/x-matroska',
            'mp3'     => 'audio/mpeg',
            'mp4'     => 'video/mp4',
            'mp4a'    => 'audio/mp4',
            'mp4v'    => 'video/mp4',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpg4'    => 'video/mp4',
            'oga'     => 'audio/ogg',
            'ogg'     => 'audio/ogg',
            'ogv'     => 'video/ogg',
            'ogx'     => 'application/ogg',
            'pbm'     => 'image/x-portable-bitmap',
            'pdf'     => 'application/pdf',
            'pgm'     => 'image/x-portable-graymap',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'ppm'     => 'image/x-portable-pixmap',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'rar'     => 'application/x-rar-compressed',
            'ras'     => 'image/x-cmu-raster',
            'rss'     => 'application/rss+xml',
            'rtf'     => 'application/rtf',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'svg'     => 'image/svg+xml',
            'swf'     => 'application/x-shockwave-flash',
            'tar'     => 'application/x-tar',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'torrent' => 'application/x-bittorrent',
            'ttf'     => 'application/x-font-ttf',
            'txt'     => 'text/plain',
            'wav'     => 'audio/x-wav',
            'webm'    => 'video/webm',
            'webp'    => 'image/webp',
            'wma'     => 'audio/x-ms-wma',
            'wmv'     => 'video/x-ms-wmv',
            'woff'    => 'application/x-font-woff',
            'wsdl'    => 'application/wsdl+xml',
            'xbm'     => 'image/x-xbitmap',
            'xls'     => 'application/vnd.ms-excel',
            'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml'     => 'application/xml',
            'xpm'     => 'image/x-xpixmap',
            'xwd'     => 'image/x-xwindowdump',
            'yaml'    => 'text/yaml',
            'yml'     => 'text/yaml',
            'zip'     => 'application/zip',
        ];

        $extension = strtolower($extension);

        return isset($mimetypes[$extension])
            ? $mimetypes[$extension]
            : null;
    }

}