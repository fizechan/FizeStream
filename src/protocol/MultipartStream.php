<?php

namespace fize\stream\protocol;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use fize\stream\StreamDecorator;
use fize\stream\StreamFactory;

/**
 * 多组件流
 *
 * 该流可用于 POST 提交数据
 */
class MultipartStream extends StreamDecorator implements StreamInterface
{

    /**
     * @var string 边界分隔符
     */
    private $boundary;

    /**
     * @var StreamFactory 流工厂
     */
    protected $factory;

    /**
     * 构造
     * @param array       $elements 组件
     * @param string|null $boundary 边界分隔符
     */
    public function __construct(array $elements = [], string $boundary = null)
    {
        $this->boundary = $boundary ?: sha1(uniqid('', true));
        $this->factory = new StreamFactory();
        $this->stream = $this->createStream($elements);
    }

    /**
     * 获取边界分隔符
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * 返回流是否可写
     * @return bool
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * 创建用于上传POST数据的聚合流
     * @param array $elements 各组件
     * @return StreamInterface
     */
    protected function createStream(array $elements): StreamInterface
    {
        $stream = new AppendStream();

        foreach ($elements as $element) {
            $this->addElement($stream, $element);
        }

        // Add the trailing boundary with CRLF
        $stream->addStream($this->factory->createStream("--$this->boundary--\r\n"));

        return $stream;
    }

    /**
     * 添加组件
     * @param AppendStream $stream  组件流对象
     * @param array        $element 对象属性
     */
    private function addElement(AppendStream $stream, array $element)
    {
        foreach (['contents', 'name'] as $key) {
            if (!array_key_exists($key, $element)) {
                throw new InvalidArgumentException("A '$key' key is required");
            }
        }

        $element['contents'] = $this->factory->createStream($element['contents']);

        if (isset($element['filename']) && empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');
            if (substr($uri, 0, 6) !== 'php://') {
                $element['filename'] = $uri;
            }
        }

        list($body, $headers) = $this->createElement(
            $element['name'],
            $element['contents'],
            $element['filename'] ?? null,
            $element['headers'] ?? []
        );

        $stream->addStream($this->factory->createStream($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream($this->factory->createStream("\r\n"));
    }

    /**
     * 创建组件
     * @param string          $name     名称
     * @param StreamInterface $stream   流对象
     * @param string|null          $filename 文件名
     * @param array           $headers  头信息
     * @return array [$stream, $headers]
     */
    private function createElement(string $name, StreamInterface $stream, ?string $filename, array $headers): array
    {
        // Set a default content-disposition header if one was no provided
        $disposition = self::getHeader($headers, 'content-disposition');
        if (!$disposition) {
            if ($filename === '0' || $filename) {
                $headers['Content-Disposition'] = sprintf('form-data; name="%s"; filename="%s"', $name, basename($filename));
            } else {
                $headers['Content-Disposition'] = "form-data; name=\"$name\"";
            }
        }

        // Set a default content-length header if one was no provided
        $length = self::getHeader($headers, 'content-length');
        if (!$length) {
            if ($length = $stream->getSize()) {
                $headers['Content-Length'] = (string)$length;
            }
        }

        // Set a default Content-Type if one was not supplied
        $type = self::getHeader($headers, 'content-type');
        if (!$type && ($filename === '0' || $filename)) {
            if ($type = self::mimetypeFromFilename($filename)) {
                $headers['Content-Type'] = $type;
            }
        }

        return [$stream, $headers];
    }

    /**
     * 根据头信息数组返回指定键名值
     * @param array  $headers 头信息
     * @param string $key     键名
     * @return mixed|null
     */
    private static function getHeader(array $headers, string $key)
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
     * 根据文件名返回 MIME
     * @param string $filename 文件名
     * @return string|null
     */
    private static function mimetypeFromFilename(string $filename): ?string
    {
        return self::mimetypeFromExtension(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * 常见文件对应的 MIME
     * @param string $extension 后缀名
     * @return string|null
     * @link http://svn.apache.org/repos/asf/httpd/httpd/branches/1.3.x/conf/mime.types
     */
    private static function mimetypeFromExtension(string $extension): ?string
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
        return $mimetypes[$extension] ?? null;
    }

    /**
     * 将头信息数组专为POST文件头
     * @param array $headers 头信息
     * @return string
     */
    private function getHeaders(array $headers): string
    {
        $str = '';
        foreach ($headers as $key => $value) {
            $str .= "$key: $value\r\n";
        }
        return "--$this->boundary\r\n" . trim($str) . "\r\n\r\n";
    }
}
