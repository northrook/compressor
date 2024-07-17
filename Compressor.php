<?php

namespace Northrook;

use Stringable;
use function gzcompress, gzuncompress, serialize, unserialize, is_string, ltrim, number_format, strlen, substr, max, min;


class Compressor implements Stringable
{
    private readonly string $type;
    private float           $initialSizeKb;
    private float           $compressedSizeKb;
    private float           $percentImprovement;

    private function __construct(
        private mixed $data,
        private int   $level,
    ) {
        $this->data();
        $this->level();
        $this->data = gzcompress( $this->data, $this->level );
        $this->percentDifference();
    }

    public function __toString() : string {
        return $this->data;
    }

    public function getReport() : string {
        return "Comressed {$this->type}, from {$this->initialSizeKb}kB to {$this->compressedSizeKb}kB, saving {$this->percentImprovement}%.";
    }

    public static function compress( mixed $data, int $level = 9 ) : Compressor {
        return new Compressor( $data, $level );
    }

    public static function decompress( mixed $data, bool $raw = false ) : mixed {
        return $raw ? gzuncompress( $data ) : unserialize( gzuncompress( $data ) );
    }

    private function data() : void {
        $this->initialSizeKb = $this->dataSizeKb();
        $this->type          = gettype( $this->data );
        $this->data          = serialize( $this->data );
    }

    private function level() : void {
        $this->level = max( min( 9, $this->level ), 0 );
    }

    private function dataSizeKb() : float {

        $bytes = (float) strlen( is_string( $this->data ) ? $this->data : serialize( $this->data ) );

        $bytes /= 1024;

        $decimals = 2;
        // If we have leading zeros
        if ( $bytes < 1 ) {
            $floating = substr( $bytes, 2 );
            $decimals += strlen( $floating ) - strlen( ltrim( $floating, '0' ) );
        }

        return number_format( $bytes, $decimals, '.', '' );
    }

    private function percentDifference() : void {
        $this->compressedSizeKb   = $this->dataSizeKb();
        $this->percentImprovement = (float) number_format(
            ( $this->initialSizeKb - $this->compressedSizeKb ) / $this->initialSizeKb * 100,
            2,
        );
    }

}