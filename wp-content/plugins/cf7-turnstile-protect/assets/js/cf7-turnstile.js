(function () {
    'use strict';

    var widgetMap = new WeakMap();
    var formStateMap = new WeakMap();

    function getWrapper(form) {
        if (!form) {
            return null;
        }

        return form.closest('.wpcf7');
    }

    function getFormId(form) {
        if (!form) {
            return null;
        }

        var hiddenField = form.querySelector('input[name="_wpcf7"]');
        if (hiddenField && hiddenField.value) {
            return String(hiddenField.value);
        }

        var wrapper = getWrapper(form);
        if (wrapper) {
            var dataId = wrapper.getAttribute('data-id');
            if (dataId) {
                return String(dataId);
            }
        }

        return null;
    }

    function getFormConfig(form) {
        if (
            typeof cf7TurnstileProtect === 'undefined' ||
            !cf7TurnstileProtect.formsConfig
        ) {
            return null;
        }

        var formId = getFormId(form);

        if (!formId) {
            return null;
        }

        if (!cf7TurnstileProtect.formsConfig[formId]) {
            return null;
        }

        return cf7TurnstileProtect.formsConfig[formId];
    }

    function hasTokenField(form) {
        return !!form.querySelector('input[name="cf-turnstile-response"]');
    }

    function setFormSubmitting(form, isSubmitting) {
        var state = formStateMap.get(form) || {};
        state.isSubmitting = isSubmitting;
        formStateMap.set(form, state);
    }

    function isFormSubmitting(form) {
        var state = formStateMap.get(form) || {};
        return !!state.isSubmitting;
    }

    function getSubmitButton(form) {
        return form.querySelector('input[type="submit"], button[type="submit"]');
    }

    function triggerNativeSubmit(form) {
        setFormSubmitting(form, false);

        var submitButton = getSubmitButton(form);

        if (submitButton) {
            submitButton.click();
        } else {
            form.submit();
        }
    }

    function getWidgetOptions(form, mode) {
        var options = {
            sitekey: cf7TurnstileProtect.siteKey,
            theme: cf7TurnstileProtect.theme || 'light',
            callback: function () {
                if (mode === 'invisible' && isFormSubmitting(form)) {
                    triggerNativeSubmit(form);
                }
            },
            'expired-callback': function () {
                setFormSubmitting(form, false);
            },
            'error-callback': function () {
                setFormSubmitting(form, false);
            }
        };

        if (mode === 'invisible') {
            options.execution = 'execute';
            options.size = 'normal';
        } else if (mode === 'non-interactive') {
            options.size = 'flexible';
        } else {
            options.size = cf7TurnstileProtect.size || 'normal';
        }

        return options;
    }

    function renderWidgetForForm(form) {
        if (
            typeof turnstile === 'undefined' ||
            typeof cf7TurnstileProtect === 'undefined'
        ) {
            return;
        }

        if (!form) {
            return;
        }

        var config = getFormConfig(form);

        if (!config || !config.enabled) {
            return;
        }

        var wrap = form.querySelector('.cf7-turnstile-wrap');
        var widgetContainer = form.querySelector('.cf7-turnstile-widget');

        if (!wrap || !widgetContainer) {
            return;
        }

        if (widgetMap.has(widgetContainer)) {
            return;
        }

        var mode = wrap.getAttribute('data-turnstile-mode') || config.mode || 'managed';

        if (mode === 'invisible') {
            wrap.style.display = 'none';
        } else {
            wrap.style.display = '';
        }

        try {
            var widgetId = turnstile.render(widgetContainer, getWidgetOptions(form, mode));
            widgetMap.set(widgetContainer, widgetId);
        } catch (e) {
        }
    }

    function renderTurnstileWidgets(context) {
        var scope = context || document;
        var forms = scope.querySelectorAll('.wpcf7 form');

        forms.forEach(function (form) {
            renderWidgetForForm(form);
        });
    }

    function resetTurnstileWidget(form) {
        if (typeof turnstile === 'undefined' || !form) {
            return;
        }

        var widgetContainer = form.querySelector('.cf7-turnstile-widget');

        if (!widgetContainer) {
            return;
        }

        var widgetId = widgetMap.get(widgetContainer);

        if (typeof widgetId === 'undefined') {
            return;
        }

        try {
            turnstile.reset(widgetId);
        } catch (e) {
        }
    }

    function executeInvisibleTurnstile(form) {
        if (typeof turnstile === 'undefined' || !form) {
            return false;
        }

        var wrap = form.querySelector('.cf7-turnstile-wrap');
        var widgetContainer = form.querySelector('.cf7-turnstile-widget');

        if (!wrap || !widgetContainer) {
            return false;
        }

        var mode = wrap.getAttribute('data-turnstile-mode') || 'managed';

        if (mode !== 'invisible') {
            return false;
        }

        var widgetId = widgetMap.get(widgetContainer);

        if (typeof widgetId === 'undefined') {
            return false;
        }

        setFormSubmitting(form, true);

        try {
            turnstile.execute(widgetId);
            return true;
        } catch (e) {
            setFormSubmitting(form, false);
            return false;
        }
    }

    function handleFormSubmit(event) {
        var form = event.target;

        if (!form || !form.matches('.wpcf7 form')) {
            return;
        }

        var config = getFormConfig(form);

        if (!config || !config.enabled) {
            return;
        }

        var wrap = form.querySelector('.cf7-turnstile-wrap');

        if (!wrap) {
            return;
        }

        var mode = wrap.getAttribute('data-turnstile-mode') || config.mode || 'managed';

        if (mode !== 'invisible') {
            return;
        }

        if (hasTokenField(form)) {
            return;
        }

        if (isFormSubmitting(form)) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        executeInvisibleTurnstile(form);
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderTurnstileWidgets(document);
    });

    document.addEventListener('submit', handleFormSubmit, true);

    document.addEventListener('wpcf7submit', function (event) {
        if (event && event.target) {
            setFormSubmitting(event.target, false);
            resetTurnstileWidget(event.target);
        }
    });

    document.addEventListener('wpcf7invalid', function (event) {
        if (event && event.target) {
            setFormSubmitting(event.target, false);
            resetTurnstileWidget(event.target);
        }
    });

    document.addEventListener('wpcf7spam', function (event) {
        if (event && event.target) {
            setFormSubmitting(event.target, false);
            resetTurnstileWidget(event.target);
        }
    });

    document.addEventListener('wpcf7mailfailed', function (event) {
        if (event && event.target) {
            setFormSubmitting(event.target, false);
            resetTurnstileWidget(event.target);
        }
    });

    document.addEventListener('wpcf7mailsent', function (event) {
        if (event && event.target) {
            setFormSubmitting(event.target, false);
            resetTurnstileWidget(event.target);
        }
    });

    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        renderTurnstileWidgets(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();