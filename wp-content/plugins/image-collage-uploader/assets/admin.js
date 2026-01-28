jQuery(function ($) {
    const $items = $('#gda-items');

    function esc(str) {
        return String(str || '').replace(/"/g, '&quot;');
    }

    function rowTemplate(item, i) {
        const attachmentId = item.attachment_id || 0;
        const thumb = item.thumb || '';

        return `
      <div class="gda-row" data-index="${i}">
        <div class="gda-thumb">
          ${thumb ? `<img src="${esc(thumb)}" alt="">` : `<div class="gda-thumb-empty">Geen afbeelding</div>`}
        </div>

        <div class="gda-fields">
          <input type="hidden" name="items[${i}][attachment_id]" class="gda-attachment-id" value="${attachmentId}">

          <div class="gda-actions">
            <button type="button" class="button button-secondary gda-pick">Vervang</button>
            <button type="button" class="button gda-remove">Verwijder</button>
          </div>

          <div class="gda-note">Je kan onbeperkt foto’s toevoegen.</div>
        </div>
      </div>
    `;
    }

    function rebuildNameIndexes() {
        $items.find('.gda-row').each(function (idx) {
            $(this).attr('data-index', idx);
            $(this).find('input.gda-attachment-id').attr('name', `items[${idx}][attachment_id]`);
        });
    }

    function addRow(item) {
        const i = $items.find('.gda-row').length;
        $items.append(rowTemplate(item, i));
        rebuildNameIndexes();
    }

    // Boot: render opgeslagen items
    const stored = (window.GDA_COLLAGE && Array.isArray(window.GDA_COLLAGE.items)) ? window.GDA_COLLAGE.items : [];

    if (stored.length) {
        stored.forEach((it) => {
            const id = parseInt(it.attachment_id || 0, 10);
            const thumb = it.thumb || '';
            addRow({ attachment_id: id, thumb });
        });
    }

    /**
     * + Foto toevoegen (1 per keer)
     */
    let addFrame = null;

    $('#gda-add-item').on('click', function () {
        addFrame = wp.media({
            title: 'Kies een foto',
            button: { text: 'Toevoegen' },
            multiple: false,
            library: { type: 'image' }
        });

        addFrame.on('select', function () {
            const attachment = addFrame.state().get('selection').first().toJSON();
            const id = attachment.id;

            let url = attachment.url;
            if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) {
                url = attachment.sizes.thumbnail.url;
            }

            addRow({ attachment_id: id, thumb: url });
        });

        addFrame.open();
    });

    // Remove row
    $items.on('click', '.gda-remove', function () {
        $(this).closest('.gda-row').remove();
        rebuildNameIndexes();
    });

    /**
     * Vervang foto per rij
     */
    $items.on('click', '.gda-pick', function () {
        const $row = $(this).closest('.gda-row');
        const $hidden = $row.find('.gda-attachment-id');
        const $thumb = $row.find('.gda-thumb');

        const frame = wp.media({
            title: 'Vervang foto',
            button: { text: 'Gebruik deze foto' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            const id = attachment.id;

            let url = attachment.url;
            if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) {
                url = attachment.sizes.thumbnail.url;
            }

            $hidden.val(id);
            $thumb.html(`<img src="${esc(url)}" alt="">`);
        });

        frame.open();
    });

    // Drag & drop sorting
    $items.sortable({
        items: '.gda-row',
        axis: 'y',
        containment: 'parent',
        tolerance: 'pointer',
        start: function (e, ui) {
            ui.item.addClass('is-dragging');
        },
        stop: function (e, ui) {
            ui.item.removeClass('is-dragging');
            rebuildNameIndexes(); // BELANGRIJK: volgorde correct opslaan
        }
    });
});
