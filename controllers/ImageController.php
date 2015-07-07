<?php
/**
 * Created by PhpStorm.
 * User: semyonchick
 * Date: 18.05.2015
 * Time: 23:48
 */

namespace rere\image\controllers;

use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Yii;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\Response;

class ImageController extends Controller
{
    public $quality = 90;
    public $dir = 'image';
    public $default = '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';

    public function actionIndex($name, $type)
    {
        $dir = Yii::getAlias("@webroot/{$this->dir}/_{$type}/");
        if (file_exists($dir . $name))
            $this->printFile($dir . $name);

        if (!file_exists($dir)) FileHelper::createDirectory($dir);

        list($width, $height) = explode('x', $type);

        $fromDir = Yii::getAlias("@webroot/{$this->dir}/tmp/");
        $from = $fromDir . $name;
        if (!file_exists($from)) {
            $name = 'default.jpg';
            $fromDir = Yii::getAlias("@runtime/");
            file_put_contents($fromDir . $name, base64_decode($this->default));
        }

        if (file_exists($dir . $name))
            $this->printFile($dir . $name);

        $image = Image::getImagine()->open(Yii::getAlias($fromDir . $name));

        // Уменьшаем размеры
        $k = $image->getSize()->getWidth() / $image->getSize()->getHeight();

        if(!$width) $width = $height * $k;
        if(!$height) $height = $width / $k;

        $newWidth = $image->getSize()->getWidth();
        if ($newWidth > $width) $newWidth = $k > 1 ? $width * $k : $width;

        $newHeight = $image->getSize()->getHeight();
        if ($newHeight > $height) $newHeight = $k < 1 ? $height / $k : $height;

        $image->resize(new Box($newWidth, $newHeight), ImageInterface::FILTER_LANCZOS);

        $box = new Box($width, $height);

        // Обрезаем лишнее
        $startX = 0;
        $startY = 0;
        $size = $image->getSize();
        if ($size->getWidth() > $width) {
            $startX = ceil($size->getWidth() - $width) / 2;
        }
        if ($size->getHeight() > $height) {
            $startY = ceil($size->getHeight() - $height) / 2;
        }

        $image->crop(new Point($startX, $startY), $box);

        // Делаем белый фон
        if ($size->getWidth() < $width || $size->getHeight() < $height) {
            $thumb = Image::getImagine()->create(new Box($width, $height), new Color('FFF', 100));

            $size = $image->getSize();

            $startX = 0;
            $startY = 0;
            if ($size->getWidth() < $width) {
                $startX = ceil($width - $size->getWidth()) / 2;
            }
            if ($size->getHeight() < $height) {
                $startY = ceil($height - $size->getHeight()) / 2;
            }
            $thumb->paste($image, new Point($startX, $startY));
        } else
            $thumb = $image;

        $thumb->interlace(ImageInterface::INTERLACE_PARTITION);

        $thumb->save($dir . $name, [
            'quality' => $this->quality,
            'png_compression_level' => 9,
        ]);

        $this->printFile($dir . $name);
    }

    public function printFile($file)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        header("Expires: " . gmdate('r', time() + 60 * 60 * 24 * 30));
        header("Last-Modified: " . gmdate('r'));
        header('Content-Type: ' . FileHelper::getMimeTypeByExtension($file, FileHelper::$mimeMagicFile));
        header("Content-Length: " . filesize($file));
        readfile($file);
    }
}