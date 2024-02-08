<?php

namespace PixelGallery\Admin;



if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

class ModuleService {



    public static function get_widget_settings($callable) {

        $settings_fields = [
            'pixel_gallery_active_modules' => [
                [
                    'name'         => 'alien',
                    'label'        => esc_html__('Alien', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/alien/',
                    'video_url'    => 'https://youtu.be/O4YS5LNJI2g',
                ],
                [
                    'name'         => 'aware',
                    'label'        => esc_html__('Aware', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/aware/',
                    'video_url'    => 'https://youtu.be/r6XFiNTDFOA',
                ],
                [
                    'name'         => 'axen',
                    'label'        => esc_html__('Axen', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/axen/',
                    'video_url'    => 'https://youtu.be/2g6YB1oRug8',
                ],
                [
                    'name'         => 'amaze',
                    'label'        => esc_html__('Amaze', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/amaze/',
                    'video_url'    => 'https://youtu.be/WPwS_wjGBIk?si=3V3eH4H2Qrn2-H3v',
                ],
                [
                    'name'         => 'craze',
                    'label'        => esc_html__('Craze', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/craze/',
                    'video_url'    => 'https://youtu.be/4_TsiUOKS64',
                ],
                [
                    'name'         => 'crop',
                    'label'        => esc_html__('Crop', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/crop/',
                    'video_url'    => 'https://youtu.be/EbxYzM47GAs',
                ],
                // [
                //     'name'         => 'coslide',
                //     'label'        => esc_html__('Coslide', 'pixel-gallery'),
                //     'type'         => 'checkbox',
                //     'default'      => 'off',
                //     'widget_type'  => 'pro',
                //     'content_type' => 'custom new',
                //     'demo_url'     => 'https://pixelgallery.pro/demo/coslide/',
                //     'video_url'    => '',
                // ],
                [
                    'name'         => 'diamond',
                    'label'        => esc_html__('Diamond', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/diamond/',
                    'video_url'    => 'https://youtu.be/a6wrnjc-tPk',
                ],
                [
                    'name'         => 'doodle',
                    'label'        => esc_html__('Doodle', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/doodle/',
                    'video_url'    => 'https://youtu.be/T9QmOd9o550',
                ],
                [
                    'name'         => 'dream',
                    'label'        => esc_html__('Dream', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/dream/',
                    'video_url'    => 'https://youtu.be/eGGVcn2LL-c?si=tRXJbJuirEjlBcLj',
                ],
                [
                    'name'         => 'elixir',
                    'label'        => esc_html__('Elixir', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/elixir/',
                    'video_url'    => 'https://youtu.be/WaKwrJX9z-g',
                ],
                [
                    'name'         => 'epoch',
                    'label'        => esc_html__('Epoch', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/epoch/',
                    'video_url'    => 'https://youtu.be/BPIfzG7RMw8',
                ],
                [
                    'name'         => 'evolve',
                    'label'        => esc_html__('Evolve', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/evolve/',
                    'video_url'    => 'https://youtu.be/yJ3HR4cNg_s?si=-SU8_FJjefyucD7n',
                ],
                [
                    'name'         => 'fabric',
                    'label'        => esc_html__('Fabric', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/fabric/',
                    'video_url'    => 'https://youtu.be/Jms62u57nMI',
                ],
                [
                    'name'         => 'fever',
                    'label'        => esc_html__('Fever', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/fever/',
                    'video_url'    => 'https://youtu.be/SyUvB6zcqp0',
                ],
                [
                    'name'         => 'fixer',
                    'label'        => esc_html__('Fixer', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/fixer/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'flame',
                    'label'        => esc_html__('Flame', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/flame/',
                    'video_url'    => 'https://youtu.be/xng4Uoskm9I',
                ],
                [
                    'name'         => 'flash',
                    'label'        => esc_html__('Flash', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/flash/',
                    'video_url'    => 'https://youtu.be/SIdaSB9mjnk?si=uyCsv08oBZdVbwdz',
                ],
                [
                    'name'         => 'fluid',
                    'label'        => esc_html__('Fluid', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/fluid/',
                    'video_url'    => 'https://youtu.be/1Ca7lCGxVdE',
                ],
                [
                    'name'         => 'floral',
                    'label'        => esc_html__('Floral', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/floral/',
                    'video_url'    => 'https://youtu.be/DWkgliNNKPA?si=LSOAZHyL-vqe2zOq',
                ],
                [
                    'name'         => 'glam',
                    'label'        => esc_html__('Glam', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/glam/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'glaze',
                    'label'        => esc_html__('Glaze', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/glaze/',
                    'video_url'    => 'https://youtu.be/AiYE9aRqTvQ',
                ],
                [
                    'name'         => 'ridex',
                    'label'        => esc_html__('Ridex', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/ridex/',
                    'video_url'    => 'https://youtu.be/6e8ELgxVmyo',
                ],
                [
                    'name'         => 'heron',
                    'label'        => esc_html__('Heron', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/heron/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'humble',
                    'label'        => esc_html__('Humble', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/humble/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'insta',
                    'label'        => esc_html__('Insta', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/insta/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'kitec',
                    'label'        => esc_html__('Kitec', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/kitec/',
                    'video_url'    => 'https://youtu.be/NJXfdAwMIxM',
                ],
                [
                    'name'         => 'koral',
                    'label'        => esc_html__('Koral', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/koral/',
                    'video_url'    => 'https://youtu.be/wR2yfKdbTbg',
                ],
                [
                    'name'         => 'lumen',
                    'label'        => esc_html__('Lumen', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/lumen/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'lunar',
                    'label'        => esc_html__('Lunar', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/lunar/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'lytical',
                    'label'        => esc_html__('Lytical', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/lytical/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'marron',
                    'label'        => esc_html__('Marron', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/marron/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'maven',
                    'label'        => esc_html__('Maven', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/maven/',
                    'video_url'    => 'https://youtu.be/Ojb7RzSmb2g?si=y-cyd379WE6JUFtM',
                ],
                [
                    'name'         => 'mastery',
                    'label'        => esc_html__('Mastery', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/mastery/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'mosaic',
                    'label'        => esc_html__('Mosaic', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/mosaic/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'mystic',
                    'label'        => esc_html__('Mystic', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/mystic/',
                    'video_url'    => 'https://youtu.be/VnFKJguCK7g',
                ],
                [
                    'name'         => 'menuz',
                    'label'        => esc_html__('Menuz', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/menuz/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'nexus',
                    'label'        => esc_html__('Nexus', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/nexus/',
                    'video_url'    => 'https://youtu.be/At7BhTM-9Gs',
                ],
                [
                    'name'         => 'ocean',
                    'label'        => esc_html__('Ocean', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/ocean/',
                    'video_url'    => 'https://youtu.be/150N81SaAHQ',
                ],
                [
                    'name'         => 'orbit',
                    'label'        => esc_html__('Orbit', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/orbit/',
                    'video_url'    => 'https://youtu.be/gleOj0ByQpc',
                ],
                [
                    'name'         => 'panda',
                    'label'        => esc_html__('Panda', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/panda/',
                    'video_url'    => 'https://youtu.be/2qnNCDyiXpg',
                ],
                [
                    'name'         => 'polo',
                    'label'        => esc_html__('Polo', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'off',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/polo/',
                    'video_url'    => 'https://youtu.be/l67_lQN9FNA?si=_SOVMq1ulBaC2m56',
                ],
                [
                    'name'         => 'pastel',
                    'label'        => esc_html__('Pastel', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'others',
                    'demo_url'     => 'https://pixelgallery.pro/demo/pastel/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'plex',
                    'label'        => esc_html__('Plex', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/plex/',
                    'video_url'    => 'https://youtu.be/RIiCTpHo0W0',
                ],
                [
                    'name'         => 'plumb',
                    'label'        => esc_html__('Plumb', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/plumb/',
                    'video_url'    => 'https://youtu.be/H4Pz6KPRuKI',
                ],
                [
                    'name'         => 'punch',
                    'label'        => esc_html__('Punch', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/punch/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'ranch',
                    'label'        => esc_html__('Ranch', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/ranch/',
                    'video_url'    => 'https://youtu.be/IBjHSszBflk',
                ],
                [
                    'name'         => 'remix',
                    'label'        => esc_html__('Remix', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/remix/',
                    'video_url'    => 'https://youtu.be/DM_VPJjn7TQ',
                ],
                
                [
                    'name'         => 'ruby',
                    'label'        => esc_html__('Ruby', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/ruby/',
                    'video_url'    => 'https://youtu.be/mQCrx2jVRRI',
                ],
                [
                    'name'         => 'shark',
                    'label'        => esc_html__('Shark', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/shark/',
                    'video_url'    => 'https://youtu.be/LqZyTJAPUmM',
                ],
                // [
                //     'name'         => 'scrovis',
                //     'label'        => esc_html__('Scrovis', 'pixel-gallery'),
                //     'type'         => 'checkbox',
                //     'default'      => 'on',
                //     'widget_type'  => 'pro',
                //     'content_type' => 'custom',
                //     'demo_url'     => 'https://pixelgallery.pro/demo/scrovis/',
                //     'video_url'    => '',
                // ],
                [
                    'name'         => 'sonic',
                    'label'        => esc_html__('Sonic', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/sonic/',
                    'video_url'    => 'https://youtu.be/8KkaSa-v8l8',
                ],
                [
                    'name'         => 'spirit',
                    'label'        => esc_html__('Spirit', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/spirit/',
                    'video_url'    => 'https://youtu.be/kBMiCVbo68w?si=H5lrUXK86gQfGYrA',
                ],
                [
                    'name'         => 'tour',
                    'label'        => esc_html__('Tour', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/tour/',
                    'video_url'    => 'https://youtu.be/kDB1kUXChP0',
                ],
                [
                    'name'         => 'trance',
                    'label'        => esc_html__('Trance', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom new',
                    'demo_url'     => 'https://pixelgallery.pro/demo/trance/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'tread',
                    'label'        => esc_html__('Tread', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/tread/',
                    'video_url'    => 'https://youtu.be/WYEDNZfwDlM?si=nsm8D8ZOuCvuAeSO',
                ],
                [
                    'name'         => 'turbo',
                    'label'        => esc_html__('Turbo', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/turbo/',
                    'video_url'    => 'https://youtu.be/2wVj9Uhgti4',
                ],
                [
                    'name'         => 'verse',
                    'label'        => esc_html__('Verse', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/verse/',
                    'video_url'    => 'https://youtu.be/yBWjJcFjLJ4',
                ],
                [
                    'name'         => 'walden',
                    'label'        => esc_html__('Walden', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/walden/',
                    'video_url'    => 'https://youtu.be/lwkQIcLuE0k',
                ],
                [
                    'name'         => 'wisdom',
                    'label'        => esc_html__('Wisdom', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/wisdom/',
                    'video_url'    => 'https://youtu.be/OsMmP5IPGKc',
                ],
                [
                    'name'         => 'xero',
                    'label'        => esc_html__('Xero', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'pro',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/xero/',
                    'video_url'    => '',
                ],
                [
                    'name'         => 'zilax',
                    'label'        => esc_html__('Zilax', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => 'on',
                    'widget_type'  => 'free',
                    'content_type' => 'custom',
                    'demo_url'     => 'https://pixelgallery.pro/demo/zilax/',
                    'video_url'    => 'https://youtu.be/XTTuvGCPnuI',
                ],

            ],

            'pixel_gallery_elementor_extend' => [
                [
                    'name'         => 'animations',
                    'label'        => esc_html__('Animations', 'pixel-gallery'),
                    'type'         => 'checkbox',
                    'default'      => "off",
                    'widget_type'  => 'free',
                    'content_type' => 'new',
                    'demo_url'     => '',
                    'video_url'    => '',
                ]
            ],

            'pixel_gallery_other_settings'   => [
                [
                    'name'  => 'minified_asset_manager_group_start',
                    'label' => esc_html__('Asset Manager', 'pixel-gallery'),
                    'desc'  => __('If you want to combine your JS and css and load in a single file so enable it. When you enable it all widgets css and JS will combine in a single file.', 'pixel-gallery'),
                    'type'  => 'start_group',
                ],

                [
                    'name'      => 'asset-manager',
                    'label'     => esc_html__('Asset Manager', 'pixel-gallery'),
                    'type'      => 'checkbox',
                    'default'   => 'off',
                    'widget_type' => 'free',
                    'demo_url'  => 'https://www.elementpack.pro/knowledge-base/how-to-use-element-pack-asset-manager/',
                    'video_url' => 'https://youtu.be/nytQFZv_CSs',
                ],

                [
                    'name' => 'minified_asset_manager_group_end',
                    'type' => 'end_group',
                ],
            ]
        ];

        $settings                    = [];
        $settings['settings_fields'] = $settings_fields;

        return $callable($settings);
    }

    private static function _is_plugin_installed($plugin, $plugin_path) {
        $installed_plugins = get_plugins();
        return isset($installed_plugins[$plugin_path]);
    }

    public static function is_module_active($module_id, $options, $module_path = BDTPG_MODULES_PATH) {
        if (!isset($options[$module_id])) {
            if (file_exists($module_path . $module_id . '/module.info.php')) {
                $module_data = require $module_path . $module_id . '/module.info.php';
                return $module_data['default_activation'];
            }
        } else {
            return $options[$module_id] == 'on';
        }
    }

    public static function is_plugin_active($plugin_path) {
        if ($plugin_path) {
            return is_plugin_active($plugin_path);
        }
    }

    public static function has_module_style($module_id, $module_path = BDTPG_MODULES_PATH) {
        if (file_exists($module_path . $module_id . '/module.info.php')) {
            $module_data = require $module_path . $module_id . '/module.info.php';

            if (isset($module_data['has_style'])) {
                return $module_data['has_style'];
            }
        }
    }

    public static function has_module_script($module_id, $module_path = BDTPG_MODULES_PATH) {
        if (file_exists($module_path . $module_id . '/module.info.php')) {
            $module_data = require $module_path . $module_id . '/module.info.php';

            if (isset($module_data['has_script'])) {
                return $module_data['has_script'];
            }
        }
    }
}
