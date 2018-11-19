<?php

namespace Showcase\Services;

class Naming
{
    /**
     * Generates a Metal Gear Solid-style name (of the format [Adjective/Noun] [Animal], e.g. Revolver Ocelot).
     *
     * @param   bool?   $proper If true, name will be returned capitalized with spaces, otherwise lower and with dashes.
     * @return  string          The name.
     */
    public static function MetalGearSolid(bool $proper = false) : string
    {
        $firstFile = resource_path('db/mgs-first.txt');
        $lastFile = resource_path('db/mgs-last.txt');

        $name = [self::randomLine($firstFile), self::randomLine($lastFile)];

        $fn  = $proper ? 'ucwords' : 'strtolower';
        $sep = $proper ? ' ' : '-';
        return $fn(implode($sep, $name));
    }

    /**
     * Gets a random line from a file.
     *
     * @param   string  $filePath   The file to get.
     * @return  string              A random line from the file.
     */
    private static function randomLine(string $filePath) : string
    {
        // TODO(@tylermenezes): There's probably a more efficient way to do this if we want to use large files.
        $file = file($filePath); 
        return trim($file[rand(0, count($file) - 1)]);
    }
}
