define(function(require) {
    'use strict';

    const _ = require('underscore');
    const error = require('oroui/js/error');
    const tools = require('oroui/js/tools');

    return {
        /**
         * Shows warning, only in DEV mode
         *
         * ```javascript
         * logger.warn('Value `{{value}}` is not valid', {value: 5});
         * ```
         *
         * @param {String} message - message to warn
         * @param {Object} variables - variables to replace in message text
         */
        warn: function(message, variables) {
            const tpl = _.template(message, {
                interpolate: /\{\{([\s\S]+?)}}/g
            });

            if (tools.debug) {
                error.showErrorInConsole(new Error('Warning: ' + tpl(variables || {})));
            }
        },

        /**
         * Throws exception with message
         *
         * ```javascript
         * logger.error('Value `{{value}}` is not valid', {value: 5});
         * ```
         *
         * @param {String} message - message for exception to throw
         * @param {Object} variables - variables to replace
         */
        error: function(message, variables) {
            const tpl = _.template(message, {
                interpolate: /{{([\s\S]+?)}}/g
            });
            throw new Error(tpl(variables || {}));
        }
    };
});
