<?php

class SeedDMS_Preview_PhpApiPreviewer extends SeedDMS_Preview_Previewer {

    protected function internalCreatePreview($mimeType, $width, $file, $target) { /* {{{ */
        // TODO implement
//        switch($mimeType) {
//            case "image/png":
//            case "image/gif":
//            case "image/jpeg":
//            case "image/jpg":
//            case "image/svg+xml":
//                $cmd = 'convert -resize '.$width.'x '.$file.' '.$target;
//                break;
//            case "application/pdf":
//            case "application/postscript":
//                $cmd = 'convert -density 100 -resize '.$width.'x '.$file.'[0] '.$target;
//                break;
//            case "text/plain":
//                $cmd = 'convert -resize '.$width.'x '.$file.'[0] '.$target;
//                break;
//            case "application/x-compressed-tar":
//                $cmd = 'tar tzvf '.$file.' | convert -density 100 -resize '.$width.'x text:-[0] '.$target;
//                break;
//        }
    } /* }}} */

}