<?php

class SeedDMS_Preview_PhpApiPreviewer extends SeedDMS_Preview_Previewer {

    protected function internalCreatePreview($mimeType, $width, $file, $target) { /* {{{ */
        switch($mimeType) {
            case "image/png":
            case "image/gif":
            case "image/jpeg":
            case "image/jpg":
            case "image/svg+xml":
                $img = new Imagick($file);
                $img->scaleImage($width, 0);
                $img->writeImage($target);
                $img->destroy();
            break;
            case "application/pdf":
            case "application/postscript":
                $img = new Imagick($file.'[0]');
                $img->setResolution(100, 100);
                $img->scaleImage($width, 0);
                $img->writeImage($target);
                $img->destroy();
                break;
            case "text/plain":
                $img = new Imagick($file.'[0]');
                $img->scaleImage($width, 0);
                $img->writeImage($target);
                $img->destroy();
                break;
        }
    } /* }}} */

}