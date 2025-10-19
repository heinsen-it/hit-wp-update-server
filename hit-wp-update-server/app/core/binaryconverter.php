<?php

namespace hitwpupdateserver\app\core;
class BinaryConverter
{
    /**
     * Konvertiert Text in Binärcode
     *
     * @param string $text Der zu konvertierende Text
     * @param string $separator Trennzeichen zwischen den Binärwerten (Standard: Leerzeichen)
     * @return string Der Text als Binärcode
     */
    public function textToBinary(string $text, string $separator = ' '): string
    {
        $binary = [];

        for ($i = 0, $iMax = strlen($text); $i < $iMax; $i++) {
            $char = $text[$i];
            $ascii = ord($char);
            $binary[] = str_pad(decbin($ascii), 8, '0', STR_PAD_LEFT);
        }

        return implode($separator, $binary);
    }

    /**
     * Konvertiert eine Zahl in Binärcode
     *
     * @param int|float $number Die zu konvertierende Zahl
     * @return string Die Zahl als Binärcode
     */
    public function numberToBinary($number): string
    {
        if (is_float($number)) {
            // Für Fließkommazahlen: Konvertiere als String
            return $this->textToBinary((string)$number);
        }

        return decbin((int)$number);
    }

    /**
     * Konvertiert Binärcode zurück in Text
     *
     * @param string $binary Der Binärcode
     * @param string $separator Trennzeichen zwischen den Binärwerten (Standard: Leerzeichen)
     * @return string Der dekodierte Text
     * @throws InvalidArgumentException wenn der Binärcode ungültig ist
     */
    public function binaryToText(string $binary, string $separator = ' '): string
    {
        $binary = trim($binary);

        if (empty($binary)) {
            return '';
        }

        // Teile den Binärcode anhand des Separators
        $binaryArray = explode($separator, $binary);
        $text = '';

        foreach ($binaryArray as $bin) {
            $bin = trim($bin);

            // Überprüfe, ob es sich um gültigen Binärcode handelt
            if (!preg_match('/^[01]+$/', $bin)) {
                throw new InvalidArgumentException("Ungültiger Binärcode: $bin");
            }

            $decimal = bindec($bin);
            $text .= chr($decimal);
        }

        return $text;
    }

    /**
     * Konvertiert Binärcode zurück in eine Zahl
     *
     * @param string $binary Der Binärcode
     * @return int Die dekodierte Zahl
     * @throws InvalidArgumentException wenn der Binärcode ungültig ist
     */
    public function binaryToNumber(string $binary): int
    {
        $binary = trim($binary);

        // Überprüfe, ob es sich um gültigen Binärcode handelt
        if (!preg_match('/^[01]+$/', $binary)) {
            throw new InvalidArgumentException("Ungültiger Binärcode: $binary");
        }

        return bindec($binary);
    }
}

?>