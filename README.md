# Compressor

A lossless data compression library using PHP `zlib`.

> [!IMPORTANT]
> This package is still in development.
>
> While it is considered MVP and stable, it may still undergo breaking changes.

## Installation

```bash
composer require northrook/compressor
``` 

## Usage

### Compressing

Compress data using the `Compressor::compress()` function, it has two arguments:

- `mixed $data` to compress. It is type-checked, and serialized using the native `\serialize` function if it us not a string,
- `int $level` of compression, from 0 to 9, defaulting to 9.

```php
$example = \Northrook\Compressor::compress( $_SERVER ) : Compressor;
$example->data;  // Resulting compressed string
$example->report // "Compressed data array, from 3.54kB to 1.41kB, saving 60.17%."
```

### Decompressing

To decompress the data, use the `decompress` static function, it has two arguments:

- `string $data` to decompress. Unserialized by default.
- `bool $raw` Returns the decompressed data as-is.

```php
$server_array = \Northrook\Compressor::decompress( $example ) : mixed;
$server_array // 
```

> [!TIP]
> Greater compression may be achieved by pre-serializing the data in some cases.
>
> Just don't forget to set `decompress( $data, true )`, and apply the appropriate deserializer externally.
>
> Arrays of HTML-like strings typically benefit from using `json_encode`, typically `~2%` smaller than `serialize`.

### Notes

The `Compressor` class is `\Stringable`, but also provides the readonly `data` property for unambiguous access.

##

## License

[MIT](https://github.com/northrook/compressor/blob/main/LICENSE)