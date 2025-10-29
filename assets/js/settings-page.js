/**
 * Settings Page JavaScript
 * Handles auction house repeater functionality
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        let rowIndex = $('#aees-auction-houses-list tr.aees-auction-house-row').length;

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
         * Form validation before submit
         */
        $('form').on('submit', function (e) {
            let isValid = true;
            const errors = [];

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
