<?php
/**
 * Configuration file for assets
 *
 * General format is "resourceName [Flags]" => array(dependencies);
 *
 * resourceName must end in either js or css
 * and can have leading path starting from
 * {Bundle}/Assets/Scripts/ or
 * {Bundle/Assets/Styles/ , depending on asset type
 *
 * flags can be:
 *  I - Inline (does not combine asset, but leaves it inline in a <script> or <style> tag}
 *  X - External {ignores the path prefix, and includes it in asset declaration to be loaded by the browser;
 *  c - do not Combine, leaves the asset to be loaded individually, and splits the combination chain
 *  m - do not Minify (skip minification for this asset,
 *      normally used when loading Environment-Suffixed versions
 *
 * defautls are:
 *  not inline,
 *  not external,
 *  always combine,
 *  always minify
 *
 *  Procedure is Minify->Combine->Gzip
 */

return [

];