<?php

declare( strict_types = 1 );

namespace Northrook;

use Stringable, LogicException;
use function gzcompress, gzuncompress, serialize, unserialize, is_string, ltrim, number_format, strlen, substr, max, min;


/**
 * @property-read string $type   Unserialized data type
 * @property-read string $data   Compressed data string
 * @property-read string $report Simple performance report; Data {type}, from {size} to {size}, saving {difference}%.
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @link   https://github.com/northrook/compressor Documentation
 */
final class Compressor implements Stringable
{
    private string $type;
    private float  $initialSizeKb;
    private float  $compressedSizeKb;
    private float  $percentImprovement;

    /**
     * @param mixed  $data   The data to compress.
     * @param int    $level  `[9]` From `0` to `9`.
     */
    private function __construct(
        private mixed $data,
        private int   $level,
    ) {
        $this->data();
        $this->level();
        $this->data = gzcompress( $this->data, $this->level );
        $this->percentDifference();
    }

    /**
     * Property accessor.
     */
    public function __get( string $property ) : string {
        return match ( $property ) {
            'type'   => $this->type,
            'data'   => $this->__toString(),
            'report' => $this->getReport(),
        };
    }

    /**
     * Check if the property exists.
     */
    public function __isset( string $property ) : bool {
        return match ( $property ) {
            'type'  => isset( $this->type ),
            'data'  => isset( $this->data ),
            default => false,
        };
    }

    /**
     * @throws LogicException when writing a property.
     */
    public function __set( string $name, mixed $value ) {
        throw new LogicException( $this::class . ' properties are read-only.' );
    }

    /**
     * Get the compressed data.
     *
     * @return string the compressed data
     *
     * @throws LogicException if the {@see $data} is not a string
     */
    public function __toString() : string {
        return is_string( $this->data )
            ? $this->data
            : throw new LogicException( "An error occured during compression." );
    }

    /**
     * @param mixed  $data   The data to compress.
     * @param int    $level  `[9]` From `0` to `9`.
     *
     * @return Compressor
     */
    public static function compress( mixed $data, int $level = 9 ) : Compressor {
        return new Compressor( $data, $level );
    }

    /**
     * @param string  $data
     * @param bool    $raw
     *
     * @return mixed
     */
    public static function decompress( string $data, bool $raw = false ) : mixed {
        return $raw ? gzuncompress( $data ) : unserialize( gzuncompress( $data ) );
    }

    private function data() : void {
        $this->initialSizeKb = $this->dataSizeKb();
        $this->type          = gettype( $this->data );
        $this->data          = is_string( $this->data ) ? $this->data : serialize( $this->data );
    }

    private function level() : void {
        $this->level = max( min( 9, $this->level ), 0 );
    }

    private function getReport() : string {
        return "Compressed data {$this->type}, from {$this->initialSizeKb}kB to {$this->compressedSizeKb}kB, saving {$this->percentImprovement}%.";
    }

    private function dataSizeKb() : float {

        $bytes = (float) strlen( is_string( $this->data ) ? $this->data : serialize( $this->data ) );

        $bytes /= 1024;

        $decimals = 2;
        // If we have leading zeros
        if ( $bytes < 1 ) {
            $floating = substr( (string) $bytes, 2 );
            // Remove leading zeros
            $decimals += strlen( $floating ) - strlen( ltrim( $floating, '0' ) );
        }

        return (float) number_format( $bytes, $decimals, '.', '' );
    }

    private function percentDifference() : void {
        $this->compressedSizeKb   = $this->dataSizeKb();
        $this->percentImprovement = (float) number_format(
            ( $this->initialSizeKb - $this->compressedSizeKb ) / $this->initialSizeKb * 100,
            2,
        );
    }

}