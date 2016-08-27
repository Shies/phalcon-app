<?php
namespace Engine;

use Phalcon\Paginator\Adapter\Model;
use Flib\Pconfig;
use Phalcon\Image\Adapter\GD as ImagePs;

class Images
{
    public static function getFileDir($basesDir, $dir, $url)
    {
        $dir = str_ireplace('_', '/', $dir);
        $dirname = $basesDir . $dir;
        $ret = array(); 
        if (is_dir($dirname)) {
            if (($dh = @opendir($dirname)) !== false) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $path = $dirname . $file;
                        $pathArray = array();
                        if (is_dir($path)) {
                            $pathArray ['path'] = str_replace('/', '_', $dir . $file . '/');
                            $pathArray ['name'] = $file;
                            $pathArray ['type'] = 'dir';
                            $pathArray ['dir'] = str_replace('/', '_', $dir . $file . '/');
                            $ret [] = $pathArray;
                        } elseif ('dir.jpg' != strtolower($file)) {
                            $pathArray ['path'] = $url . $dir . $file;
                            $pathArray ['name'] = $file;
                            $pathArray ['type'] = 'file';
                            $pathArray ['dir'] = $url . $dir . $file;
                            $ret [] = $pathArray;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $ret;
    }

    /*
     * 根据指定的尺寸缩放图片，不关心原图比例
     */
    static public function thumbnail($path, $width, $height, $dstImgPath)
    {
        if (ImagePs::check()) {
            $image = new ImagePs ($path);
            $image->resize($width, $height);
            if ($image->save()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * 自动根据指定的尺寸缩放图片,会根据原图长宽最小尺寸缩放，较长的一方会被裁剪
     */
    static public function autoThumbnail($path, $width, $height, $dstImgPath)
    {
        if (ImagePs::check()) {
            $image = new ImagePs();
            $imgWidth = $image->getWidth();
            $imgHeight = $image->getHeight();
            // 计算拉伸尺寸
            $widthRatio = $width / $imgWidth;
            $heightRatio = $height / $imgHeight;
            if ($widthRatio > $heightRatio) {
                $newWidth = $width;
                $newHeight = $imgHeight * $widthRatio;
            } else {
                $newHeight = $height;
                $newWidth = $imgWidth * $heightRatio;
            }
            // 最终裁剪
            $image->resize($newWidth, $newHeight);
            $image->crop($width, $height, ($newWidth - $width) / 2, ($newHeight - $height) / 2);
            if ($image->save($dstImgPath)) {
                return true;
            } else {
                return false;
            }
        } else {
            return FALSE;
        }
    }

    static public function deleteImage($file)
    {
        $baseConfig = Pconfig::get('config');
        $fullFileName = trim($baseConfig ['dir'] ['pubDir'], '/') . $file;
        if (file_exists($fullFileName)) {
            if (unlink($fullFileName)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    static public function editorImage($file, $oldFile)
    {
        $imageConfig = Pconfig::get('config', 'images');
        $baseConfig = Pconfig::get('config');
        $fullFileName = trim($baseConfig ['dir'] ['pubDir'], '/') . $oldFile;

        if (in_array($file->getType(), $config ['imageType'])) {
            if (file_exists($fullFileName)) {
                $upInfo ['size'] = $file->getSize();
                $upInfo ['state'] = 'fileExists';
                if ($file->moveTo($fullFileName)) {
                    if (ImagePs::check()) {
                        $image = new ImagePs ($fileName);
                        $upInfo ['width'] = $image->getWidth();
                        $upInfo ['height'] = $image->getHeight();
                        $upInfo ['type'] = $image->getType();
                        $upInfo ['url'] = $url;
                        $upInfo ['state'] = 'success';
                    } else {
                        $upInfo ['width'] = '';
                        $upInfo ['height'] = '';
                        $upInfo ['type'] = '';
                        $upInfo ['url'] = $url;
                        $upInfo ['state'] = '上传成功，但是您没有开启PHP的GD扩展，图片无法编辑和获取基本信息！';
                    }
                } else {
                    $upInfo ['state'] = 'moveTmp';
                }
            } else {
                $upInfo ['state'] = 'fileNoExists';
            }
        } else {
            $upInfo ['state'] = 'typeCheck';
            $upInfo ['width'] = '';
            $upInfo ['height'] = '';
            $upInfo ['type'] = '';
            $upInfo ['url'] = '';
            $upInfo ['state'] = '';
        }
        return $upInfo;
    }

    static public function addImage($file)
    {
        $imageConfig = Pconfig::get('config', 'images');
        $baseConfig = Pconfig::get('config');
        $upInfo = array();
        if (true) {
            $upInfo ['size'] = $file->getSize();

            $folder = $baseConfig ["dir"] ['imageBaseDir'];
            $dir = date('Y/m/d');
            if (!file_exists($folder . $dir)) {
                if (!mkdir($folder . $dir, 0777, true)) {
                    $upInfo ['state'] = 'mkDir';
                }
            }

            $fileName = time() . rand(1, 10000) . strtolower(strrchr($file->getName(), '.'));
            $url = '/images/' . $dir . '/' . $fileName;
            $fileName = $folder . $dir . '/' . $fileName;

            if ($file->moveTo($fileName)) {
                if (ImagePs::check()) {
                    $image = new ImagePs ($fileName);
                    $upInfo ['width'] = $image->getWidth();
                    $upInfo ['height'] = $image->getHeight();
                    $upInfo ['type'] = $image->getType();
                    $upInfo ['url'] = $url;
                    $upInfo ['state'] = 'success';
                } else {
                    $upInfo ['width'] = '';
                    $upInfo ['height'] = '';
                    $upInfo ['type'] = '';
                    $upInfo ['url'] = $url;
                    $upInfo ['state'] = '上传成功，但是您没有开启PHP的GD扩展，图片无法编辑和获取基本信息！';
                }
            } else {
                $upInfo ['state'] = 'moveTmp';
            }
        } else {
            $upInfo ['state'] = 'typeCheck';
            $upInfo ['width'] = '';
            $upInfo ['height'] = '';
            $upInfo ['type'] = '';
            $upInfo ['url'] = '';
            $upInfo ['state'] = '';
        }
        return $upInfo;
    }
}