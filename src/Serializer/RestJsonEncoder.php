<?php declare(strict_types=1);


namespace SdsDev\RestBundleDoctrine\Serializer;

use SdsDev\RestBundleDoctrine\Defaults\Defaults;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class RestJsonEncoder implements EncoderInterface, DecoderInterface
{

    public const FORMAT = Defaults::SERIALIZE_FORMAT;

    protected JsonEncode $encodingImpl;
    protected JsonDecode $decodingImpl;

    private array $defaultContext = [
        JsonDecode::ASSOCIATIVE => true,
    ];

    public function __construct(?JsonEncode $encodingImpl = null, ?JsonDecode $decodingImpl = null, array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        $this->encodingImpl = $encodingImpl ?? new JsonEncode($this->defaultContext);
        $this->decodingImpl = $decodingImpl ?? new JsonDecode($this->defaultContext);
    }

    public function encode(mixed $data, string $format, array $context = []): string
    {
        $context = array_merge($this->defaultContext, $context);

        return $this->encodingImpl->encode($data, self::FORMAT, $context);
    }

    public function decode(string $data, string $format, array $context = []): mixed
    {
        $context = array_merge($this->defaultContext, $context);

        return $this->decodingImpl->decode($data, self::FORMAT, $context);
    }

    public function supportsEncoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}