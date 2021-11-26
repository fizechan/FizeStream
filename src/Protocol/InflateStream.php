<?php

namespace Fize\Stream\Protocol;

use Fize\Stream\Stream;
use Fize\Stream\StreamDecorator;
use Fize\Stream\StreamWrapper;
use Psr\Http\Message\StreamInterface;

/**
 * 解压流
 */
class InflateStream extends StreamDecorator implements StreamInterface
{

    /**
     * 构造
     * @param StreamInterface $stream 流对象
     */
    public function __construct(StreamInterface $stream)
    {
        // read the first 10 bytes, ie. gzip header
        $header = $stream->read(10);
        $filenameHeaderLength = $this->getLengthOfPossibleFilenameHeader($stream, $header);
        // Skip the header, that is 10 + length of filename + 1 (nil) bytes
        $stream = new LimitStream($stream, -1, 10 + $filenameHeaderLength);
        $resource = StreamWrapper::getResource($stream);
        stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ);
        $this->stream = $stream->isSeekable() ? new Stream($resource) : new NoSeekStream(new Stream($resource));
    }

    /**
     * 获取文件头长度
     * @param StreamInterface $stream 流对象
     * @param string          $header 文件头
     * @return int
     */
    private function getLengthOfPossibleFilenameHeader(StreamInterface $stream, string $header): int
    {
        $filename_header_length = 0;

        if (substr(bin2hex($header), 6, 2) === '08') {
            $filename_header_length = 1;
            while ($stream->read(1) !== chr(0)) {
                $filename_header_length++;
            }
        }

        return $filename_header_length;
    }
}
