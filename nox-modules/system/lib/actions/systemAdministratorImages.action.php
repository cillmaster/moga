<?php
class systemAdministratorImagesAction extends noxAction {

    public $cache = false;

    private function resizeWithCompress($file, $w, $target = false) {
        $im = new Imagick($file);
        $width = $im->getImageWidth();
        $height = $im->getImageHeight();
        if($width > $w){
            $h = round($height * $w / $width);
            if(!$target){
                $target = $file;
            }
            $im->resizeImage($w, $h, Imagick::FILTER_LANCZOS, 1, true);
            $im->writeImage($target);
            file_put_contents($target, $this->compressPng($target));
            return true;
        }
        return false;
    }

    private function compressPng($file){ // https://pngquant.org/php.html
        return shell_exec('pngquant --quality=60-90 - < ' . escapeshellarg($file));
    }

    private function checkDir($file){
        $dir = explode('/', $file);
        array_pop($dir);
        $dir = join('/', $dir);
        if(!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }
    }

    public function check208($preview){
        if(preg_match('/prepay/', $preview)){
            return false;
        }
        $filePath = noxRealPath($preview);
        $pr208 = str_replace(
            ['/preview/', '.png'],
            ['/mini-preview/','-blueprint-preview208.png'],
            $preview
        );
        $targetPath = noxRealPath($pr208);
        if(!file_exists($targetPath)){
            $this->checkDir($targetPath);
            return $this->resizeWithCompress($filePath, 208, $targetPath);
        }
        return false;
    }

    public function previewsDownWidthTo208(){
        $fn = 'log/' . date('Y_m_d_H_i_s') . '_log_208_resize.txt';
        if($f = fopen(noxRealPath($fn), 'w')) {
            $modelVectors = new printsVectorModel();
            $vectors = $modelVectors->where(['prepay' => '0'])->fetchAll();
            foreach ($vectors as $item){
                if($this->check208($item['preview'])){
                    fwrite($f, join(' ', [$item['id'], $item['preview']]) . PHP_EOL);
                }
            }
            fclose($f);
        }
    }

    public function previewsDownWidthTo448(){
        $fn = 'log/' . date('Y_m_d_H_i_s') . '_log_448_resize.txt';
        if($f = fopen(noxRealPath($fn), 'w')) {
            $modelVectors = new printsVectorModel();
            $vectors = $modelVectors->where(['prepay' => '0'])->fetchAll();
            foreach ($vectors as $item){
                $filePath = noxRealPath($item['preview']);
                if($this->resizeWithCompress($filePath, 448)){
                    fwrite($f, join(' ', [$item['id'], $item['preview']]) . PHP_EOL);
                }
            }
            fclose($f);
        } else {
            echo $fn . ' err';
        }
    }
}