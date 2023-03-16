<?php
/**
 * Главная страница
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsDefaultDefaultAction extends noxThemeAction
{
    public function execute()
    {
        $this->title = 'Car blueprints reference and producer of vector drawings for 3d and wrap on getoutlines.com';
        //$this->addMetaDescription('Outlines is a large online blueprints and drawings reference. Thousands of free bitmap raster blueprints and hundreds of vector scalable drawings.');
        $this->addMetaDescription('We focus on car blueprints only. Get blueprint of car and auto vector drawings. '
            . 'Download free bitmaps with no limit, purchase premium quality blueprints or request any vehicle. '
            . 'Online images reference. We help designers and 3d artists to sketch and create car vector design, '
            . '3d art and modeling.');
        //$this->addMetaKeywords('outlines, getoutlines');
        $this->addMetaKeywords('blueprints, drawings, templates, free blueprints, free drawings, free templates, '
        . 'car blueprints, car blueprints free, car wrap, plans, body wrap, wrap, car wrapping, corporate fleet identity, '
        . 'car branding, 3d model, car 3d, car 3d design, car t-shirt, custom t-shirt, t-shirt printing, custom cake, '
        . 'cake topper, cake printing, car line drawings, car lettering, car vinyl, custom car paint, custom wrap, '
        . 'signwriter, full wrap, car clip arts, car mockups, outlines, getoutlines');

        foreach (['confirm_email', 'confirm_email_fb', 'reset_pass', 'reset_pass_fin', 'unsubscribe'] as $item){
            if(isset($_COOKIE[$item])) {
                $this->addVar($item, $_COOKIE[$item]);
                setcookie($item, '', time() - 1000, '/');
            }
        }

        $this->addVar('makeCars', (new printsMakeModel())->where([
            'class_id' => 1
        ])->order('name')->fetchAll());
        $this->addVar('makeTopCars', (new printsMakeModel())->where([
            'class_id' => 1,
            'top' => 1
        ])->order('name')->fetchAll());

        $this->addVar('canonical', explode('?', noxSystem::$fullUrl)[0]);

        $relVectors = (new printsVectorModel())->getRelated(16);
        shuffle($relVectors);
        $this->addVar('relVectors', $relVectors);
    }
}
