(function ($) {
    $(document).ready(function () {

        // Schadeformulier
        $('.cform .fusion-button:not(.wpcf7-submit)').parent().addClass('p-flex');

        // Voeg progress bar toe
        $('.schade-form form .contact-form').prepend('<div class="percent-bar"><span class="percent"></span></div>');

        // Initieer formulier
        $('select#schade-options').on('change', function () {
            $('.contact-form.active').removeClass('active fadeInUp animated');
            var value = $(this).val();
            var cf7 = $('#' + value);
            cf7.addClass('active fadeInUp animated');
            cf7.find('.reset-button').trigger('click');
            cf7.find('.cform').hide().removeClass('fadeOut');
            cf7.find('.cform.one').show();

            var stepCount = cf7.find('.cform').length;
            var stepWidth = cf7.find('.percent-bar').width() / stepCount;
            cf7.find('.percent').css('width', stepWidth);
            $('.fusion-alert .fusion-alert-content-wrapper .fusion-alert-content').text('');
            $('.wpcf7 .wpcf7-response-output').removeClass('is-visible').text('');
        });

        // Ga naar volgende stap
        $('.contact-form').on('click', 'a.next', function (e) {
            e.preventDefault();
            var currentForm = $(this).closest('.contact-form');
            var currentStep = $(this).closest('.cform');
            var inputs = currentStep.find(':input[required], .wpcf7-validates-as-required');
            var valid = true;

            inputs.each(function () {
                if (!$(this).val()) {
                    $(this).addClass('red-border');
                    valid = false;
                } else {
                    $(this).removeClass('red-border');
                }
            });

            if (!valid) return;

            // Ga naar volgende stap
            var totalSteps = currentForm.find('.cform').length;
            var stepSize = currentForm.find('.percent-bar').width() / totalSteps;
            var currentPercent = currentForm.find('.percent').width();

            currentStep.addClass('fadeOut');
            setTimeout(function () {
                currentStep.hide().removeClass('fadeOut');
                currentStep.next('.cform').fadeIn();
                currentForm.find('.percent').css('width', currentPercent + stepSize);
            }, 500);
        });

        // Ga naar vorige stap
        $('.contact-form').on('click', 'a.back', function (e) {
            e.preventDefault();
            var currentForm = $(this).closest('.contact-form');
            var currentStep = $(this).closest('.cform');

            var totalSteps = currentForm.find('.cform').length;
            var stepSize = currentForm.find('.percent-bar').width() / totalSteps;
            var currentPercent = currentForm.find('.percent').width();

            currentStep.addClass('fadeOut');
            setTimeout(function () {
                currentStep.hide().removeClass('fadeOut');
                currentStep.prev('.cform').fadeIn();
                currentForm.find('.percent').css('width', currentPercent - stepSize);
            }, 500);
        });

        // Remove red-border live bij invullen
        $(document).on('input change', '.wpcf7-validates-as-required, [required]', function () {
            if ($(this).val()) {
                $(this).removeClass('red-border');
            }
        });

        // Bij submit, ga terug naar eerste foutieve stap
        $('.contact-form.schade').on('click', '.wpcf7-submit', function (e) {
            var form = $(this).closest('.contact-form');
            var steps = form.find('.cform');
            var firstErrorStep = null;
            var maxFileSize = 10 * 1024 * 1024; // 10 MB

            steps.each(function () {
                var inputs = $(this).find(':input[required], .wpcf7-validates-as-required');
                var invalidFields = inputs.filter(function () {
                    return !$(this).val();
                });

                if (invalidFields.length > 0 && !firstErrorStep) {
                    firstErrorStep = $(this);
                    invalidFields.addClass('red-border');
                }

                // Check bestandsgrootte van mfile
                var fileInputs = $(this).find('input[type="file"]');
                fileInputs.each(function () {
                    var files = this.files;
                    if (files.length > 0) {
                        for (var i = 0; i < files.length; i++) {
                            if (files[i].size > maxFileSize) {
                                $(this).addClass('red-border');
                                if (!firstErrorStep) firstErrorStep = $(this).closest('.cform');
                                // Toon optionele melding
                                alert("Bestand '" + files[i].name + "' is te groot. Maximumgrootte is 10MB.");
                                break;
                            }
                        }
                    }
                });
            });

            if (firstErrorStep) {
                e.preventDefault();

                // Toon juiste stap
                steps.hide();
                firstErrorStep.show();

                // Update progress bar
                var stepIndex = steps.index(firstErrorStep) + 1;
                var totalSteps = steps.length;
                var stepSize = form.find('.percent-bar').width() / totalSteps;
                form.find('.percent').css('width', stepIndex * stepSize);

                // Scroll & shake
                $('html, body').animate({
                    scrollTop: firstErrorStep.offset().top - 100
                }, 500);
                firstErrorStep.addClass('shake');
                setTimeout(() => firstErrorStep.removeClass('shake'), 1000);
            }
        });

        (function($){
            // helper: toon melding (werkt ook met Avada alert)
            function setResponseMessage($root, msg){
                var $resp    = $root.find('.wpcf7-response-output');
                var $content = $resp.find('.fusion-alert-content');
                if ($content.length) { $content.text(msg); } else { $resp.text(msg); }
                $resp.addClass('is-visible');
            }

            // stap-handler
            function goToFirstInvalidStep($formEl){
                // 1) eerste fout binnen DIT form (input/select/textarea/radio)
                var $first = $formEl.find('.wpcf7-not-valid').first();
                if (!$first.length) {
                    var $tip = $formEl.find('.wpcf7-not-valid-tip').first();
                    if ($tip.length) $first = $tip.closest('.wpcf7-form-control-wrap');
                }
                if (!$first.length) return false;

                // 2) stap + container binnen DIT form
                var $step    = $first.closest('.cform');
                var $contact = $step.closest('.contact-form');
                if (!$step.length || !$contact.length) return false;

                // 3) juiste stap tonen
                var $steps = $contact.find('.cform');
                $steps.hide();
                $step.show();

                // 4) progressbar updaten
                var idx   = Math.max(1, $steps.index($step) + 1);
                var total = $steps.length || 1;
                var barW  = $contact.find('.percent-bar').outerWidth() || 0;
                if (barW) $contact.find('.percent').css('width', (barW / total) * idx);

                // 5) melding + scroll + focus
                var $root = $formEl.closest('.wpcf7');
                setResponseMessage($root, '⚠️ Er staat een fout op stap ' + idx + '. Corrigeer de velden en probeer opnieuw.');

                var $focus = $first.is(':input') ? $first : $first.find(':input').first();
                $('html, body').animate({ scrollTop: $step.offset().top - 100 }, 350);
                if ($focus.length) setTimeout(function(){ $focus.trigger('focus'); }, 200);

                // visuele hint
                $step.addClass('shake'); setTimeout(function(){ $step.removeClass('shake'); }, 600);
                return true;
            }

            // Fallback: na klik op submit, wacht tot CF7 klaar is met valideren
            $(document).on('click', '.wpcf7 .wpcf7-submit', function(){
                var $formEl = $(this).closest('form.wpcf7-form');
                if (!$formEl.length) return;

                var tries = 0;
                var timer = setInterval(function(){
                    tries++;

                    var status = $formEl.attr('data-status'); // "", "invalid", "sent", "submitting"
                    if (status === 'invalid') {
                        clearInterval(timer);
                        goToFirstInvalidStep($formEl);
                    } else if (status === 'sent' || tries > 40) { // ~2s timeout (40*50ms)
                        clearInterval(timer);
                    }
                }, 50);
            });
        })(jQuery);

        document.addEventListener('wpcf7mailsent', function(e){
            var unitTag = e.detail && e.detail.unitTag ? e.detail.unitTag : null;
            var $root   = unitTag ? $('#' + unitTag) : $(e.target);
            var $formEl = $root.find('form.wpcf7-form');
            if (!$formEl.length) return;

            // Bericht invullen (Avada of standaard)
            var msg = (e.detail.apiResponse && e.detail.apiResponse.message) || '✅ Bericht verzonden. Bedankt!';
            var $resp    = $root.find('.wpcf7-response-output');
            var $content = $resp.find('.fusion-alert-content');
            if ($content.length) { $content.text(msg); } else { $resp.text(msg); }
            $resp.addClass('is-visible');

            // UI resetten
            $formEl.find('.field-error-global').remove();
            $formEl.find('.red-border').removeClass('red-border');

            var $contact = $formEl.find('.contact-form.active');
            if ($contact.length) {
                var $steps = $contact.find('.cform');
                $steps.hide();
                $contact.find('.cform.one').show();
                var total = $steps.length || 1;
                var barW  = $contact.find('.percent-bar').outerWidth() || 0;
                if (barW && total) {
                    $contact.find('.percent').css('width', (barW / total));
                }
            }

        }, false);

});
})(jQuery);

