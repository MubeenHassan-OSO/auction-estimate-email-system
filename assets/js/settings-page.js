/**
 * Settings Page JavaScript
 * Handles auction house repeater functionality
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        let rowIndex = $('#aees-auction-houses-list tr.aees-auction-house-row').length;
        let providerIndex = $('#aees-service-providers-list tr.aees-service-provider-row').length;
        let mediaUploader;

        /**
         * Add new auction house row
         */
        $('#aees-add-auction-house').on('click', function () {
            // Remove the "no houses" message if it exists
            $('.aees-no-houses-row').remove();

            const newRow = `
                <tr class="aees-auction-house-row">
                    <td>
                        <input type="text"
                               name="aees_auction_houses[${rowIndex}][name]"
                               value=""
                               class="regular-text aees-house-name"
                               placeholder="e.g., Christie's"
                               required />
                    </td>
                    <td>
                        <input type="email"
                               name="aees_auction_houses[${rowIndex}][email]"
                               value=""
                               class="regular-text aees-house-email"
                               placeholder="e.g., shipping@christies.com"
                               required />
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="button aees-remove-house" title="Remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
            `;

            $('#aees-auction-houses-list').append(newRow);
            rowIndex++;

            // Focus on the newly added name field
            $('#aees-auction-houses-list tr:last-child .aees-house-name').focus();
        });

        /**
         * Remove auction house row
         */
        $(document).on('click', '.aees-remove-house', function () {
            const $row = $(this).closest('tr');
            const isSaved = $row.hasClass('aees-saved-row');
            const auctionName = $row.find('.aees-house-name').val();

            let confirmMessage = 'Are you sure you want to remove this auction house?';
            if (isSaved) {
                confirmMessage = `Are you sure you want to delete "${auctionName}"?\n\nThis action cannot be undone. Don't forget to click "Save Settings" to apply the change.`;
            }

            // Confirm before removing
            if (confirm(confirmMessage)) {
                $row.remove();

                // If no rows left, show the "no houses" message
                if ($('#aees-auction-houses-list tr.aees-auction-house-row').length === 0) {
                    const noHousesRow = `
                        <tr class="aees-no-houses-row">
                            <td colspan="3" style="text-align: center; padding: 20px; color: #999;">
                                No auction houses added yet. Click "Add Auction House" below to get started.
                            </td>
                        </tr>
                    `;
                    $('#aees-auction-houses-list').append(noHousesRow);
                }
            }
        });

        /**
         * Add new service provider row
         */
        $('#aees-add-service-provider').on('click', function () {
            // Remove the "no providers" message if it exists
            $('.aees-no-providers-row').remove();

            const newRow = `
                <tr class="aees-service-provider-row">
                    <td>
                        <input type="text"
                               name="aees_service_providers[${providerIndex}][name]"
                               value=""
                               class="regular-text aees-provider-name"
                               placeholder="e.g., Standard Shipping"
                               required />
                    </td>
                    <td>
                        <div class="aees-provider-image-wrapper" style="display: flex; align-items: center; gap: 10px;">
                            <input type="hidden"
                                   name="aees_service_providers[${providerIndex}][image]"
                                   value=""
                                   class="aees-provider-image-url" />
                            <div class="aees-provider-image-preview" style="flex-shrink: 0;">
                                <div style="width: 60px; height: 60px; border: 2px dashed #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">No Image</div>
                            </div>
                            <button type="button" class="button aees-upload-provider-image" data-readonly="false">
                                <span class="dashicons dashicons-format-image"></span> Upload
                            </button>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="button aees-remove-provider" title="Remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
            `;

            $('#aees-service-providers-list').append(newRow);
            providerIndex++;

            // Focus on the newly added name field
            $('#aees-service-providers-list tr:last-child .aees-provider-name').focus();
        });

        /**
         * Remove service provider row
         */
        $(document).on('click', '.aees-remove-provider', function () {
            const $row = $(this).closest('tr');
            const isSaved = $row.hasClass('aees-saved-row');
            const providerName = $row.find('.aees-provider-name').val();

            let confirmMessage = 'Are you sure you want to remove this service provider?';
            if (isSaved) {
                confirmMessage = `Are you sure you want to delete "${providerName}"?\n\nThis action cannot be undone. Don't forget to click "Save Settings" to apply the change.`;
            }

            // Confirm before removing
            if (confirm(confirmMessage)) {
                $row.remove();

                // If no rows left, show the "no providers" message
                if ($('#aees-service-providers-list tr.aees-service-provider-row').length === 0) {
                    const noProvidersRow = `
                        <tr class="aees-no-providers-row">
                            <td colspan="3" style="text-align: center; padding: 20px; color: #999;">
                                No service providers added yet. Click "Add Service Provider" below to get started.
                            </td>
                        </tr>
                    `;
                    $('#aees-service-providers-list').append(noProvidersRow);
                }
            }
        });

        /**
         * WordPress Media Uploader for Service Provider Images
         */
        $(document).on('click', '.aees-upload-provider-image', function (e) {
            e.preventDefault();

            const $button = $(this);
            const $wrapper = $button.closest('.aees-provider-image-wrapper');
            const $imageInput = $wrapper.find('.aees-provider-image-url');
            const $imagePreview = $wrapper.find('.aees-provider-image-preview');
            const isReadonly = $button.attr('data-readonly') === 'true';

            // Don't allow upload if readonly (saved provider)
            if (isReadonly) {
                return;
            }

            // Create the media uploader
            mediaUploader = wp.media({
                title: 'Choose Service Provider Icon/Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected, run a callback
            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                const imageUrl = attachment.url;

                // Update the hidden input
                $imageInput.val(imageUrl);

                // Update the preview
                $imagePreview.html(`<img src="${imageUrl}" alt="Provider Icon" style="max-width: 60px; max-height: 60px; border: 1px solid #ddd; border-radius: 4px; display: block;" />`);

                // Update button text
                $button.html('<span class="dashicons dashicons-format-image"></span> Change');

                // Add remove button if it doesn't exist
                if ($wrapper.find('.aees-remove-provider-image').length === 0) {
                    $button.after(`
                        <button type="button" class="button aees-remove-provider-image" data-readonly="false">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    `);
                }
            });

            // Open the uploader dialog
            mediaUploader.open();
        });

        /**
         * Remove service provider image
         */
        $(document).on('click', '.aees-remove-provider-image', function (e) {
            e.preventDefault();

            const $button = $(this);
            const $wrapper = $button.closest('.aees-provider-image-wrapper');
            const $imageInput = $wrapper.find('.aees-provider-image-url');
            const $imagePreview = $wrapper.find('.aees-provider-image-preview');
            const $uploadBtn = $wrapper.find('.aees-upload-provider-image');
            const isReadonly = $button.attr('data-readonly') === 'true';

            // Don't allow removal if readonly (saved provider)
            if (isReadonly) {
                return;
            }

            // Clear the hidden input
            $imageInput.val('');

            // Clear the preview
            $imagePreview.html('<div style="width: 60px; height: 60px; border: 2px dashed #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 11px;">No Image</div>');

            // Update upload button text
            $uploadBtn.html('<span class="dashicons dashicons-format-image"></span> Upload');

            // Remove the remove button
            $button.remove();
        });

        /**
         * Form validation before submit
         */
        $('form').on('submit', function (e) {
            let isValid = true;
            const errors = [];

            // Validate only new (non-saved) service provider rows
            $('.aees-service-provider-row').not('.aees-saved-row').each(function () {
                const name = $(this).find('.aees-provider-name').val().trim();

                if (name === '') {
                    isValid = false;
                    errors.push('All service provider names must be filled out.');
                    return false; // Break the loop
                }
            });

            // Validate only new (non-saved) auction house rows
            $('.aees-auction-house-row').not('.aees-saved-row').each(function () {
                const name = $(this).find('.aees-house-name').val().trim();
                const email = $(this).find('.aees-house-email').val().trim();

                if (name === '' || email === '') {
                    isValid = false;
                    errors.push('All auction house fields must be filled out.');
                    return false; // Break the loop
                }

                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    isValid = false;
                    errors.push(`Invalid email address: ${email}`);
                    return false; // Break the loop
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert(errors.join('\n'));
                return false;
            }

            return true;
        });
    });

})(jQuery);
