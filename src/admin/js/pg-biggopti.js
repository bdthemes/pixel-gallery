jQuery(document).ready(function ($) {
    // Delegate to capture dynamically injected biggopties as well
    $(document).on('click', '.pixel-gallery-biggopti.is-dismissible .bdt-biggopti-dismiss', function () {
        $this = $(this).parents('.pixel-gallery-biggopti');
        var $id = $this.attr('id') || '';
        var $time = $this.attr('dismissible-time') || '';
        var $meta = $this.attr('dismissible-meta') || '';
        $.ajax({
            url: (window.PixelGalleryBiggoptiConfig && PixelGalleryBiggoptiConfig.ajaxurl) ? PixelGalleryBiggoptiConfig.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : ''),
            type: 'POST',
            data: {
                action: 'pixel-gallery-biggopties',
                id: $id,
                meta: $meta,
                time: $time,
                _wpnonce: PixelGalleryBiggoptiConfig.nonce,
            }
        });
    });
});