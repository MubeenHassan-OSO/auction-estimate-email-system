jQuery(document).ready(function ($) {
    const container = $("#aees-proposals-wrap");
    const saveBtn = $("#aees-save-entry");
    const emailBtn = $("#aees-send-email");
    const addBtn = $("#aees-add-proposal");
    const auctionHouseSelect = $("#aees-auction-house-select");
    const auctionNameInput = $("#aees-auction-name");
    const auctionEmailInput = $("#aees-auction-email");
    let unsavedChanges = false;

    // Handle auction house dropdown selection
    auctionHouseSelect.on("change", function () {
        const selectedOption = $(this).find("option:selected");
        const auctionName = selectedOption.data("name") || "";
        const auctionEmail = selectedOption.data("email") || "";

        // Update hidden fields
        auctionNameInput.val(auctionName);
        auctionEmailInput.val(auctionEmail);

        // Remove error styling if present
        $(this).css("border-color", "");

        // Mark as unsaved
        setUnsaved(true);
    });

    // helpers
    function setUnsaved(flag) {
        unsavedChanges = !!flag;
        updateButtonStates();
    }

    // Validate email format
    function isValidEmail(email) {
        if (!email || email.trim() === '') return false; // Empty is NOT allowed - required field
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Validate price format (numbers, decimals, optional dollar sign)
    function isValidPrice(price) {
        if (!price || price.trim() === '') return false;
        // Remove dollar signs and commas, then check if it's a valid number
        const cleanPrice = price.replace(/[$,]/g, '').trim();
        return !isNaN(cleanPrice) && parseFloat(cleanPrice) >= 0;
    }

    // Format price input to show dollar sign
    function formatPriceInput(input) {
        let value = input.val().replace(/[^0-9.]/g, ''); // Remove non-numeric except decimal
        if (value) {
            input.val(value);
        }
    }

    function updateButtonStates() {
        const proposals = container.find(".aees-proposal-card");
        const filledProposals = proposals.filter(function () {
            const serviceProvider = $(this).find("select[name*='[service_provider]']").val()?.trim();
            const price = $(this).find("input[name*='[price]']").val()?.trim();
            const details = $(this).find("textarea[name*='[details]']").val()?.trim();
            return serviceProvider && price && details;
        });

        // Check if user has responded (entry is in readonly mode)
        const isReadOnly = $(".aees-edit-entry-container").hasClass("aees-entry-readonly");

        // Check if entry is closed
        const isClosed = aeesData.entry_status === 'closed';

        // If readonly, disable all action buttons
        if (isReadOnly) {
            saveBtn.prop("disabled", true);
            emailBtn.prop("disabled", true);
            addBtn.prop("disabled", true);
            return;
        }

        // If entry is closed, disable all action buttons
        if (isClosed) {
            saveBtn.prop("disabled", true).attr("title", "Entry is closed - reopen to edit");
            emailBtn.prop("disabled", true).attr("title", "Entry is closed - reopen to send");
            addBtn.prop("disabled", true).attr("title", "Entry is closed - reopen to add proposals");
            return;
        }

        // Check if email is sent and not expired - disable save and add buttons
        const emailStatus = aeesData.email_status;
        const isEmailSent = emailStatus && emailStatus.is_sent && !emailStatus.is_expired;

        if (isEmailSent) {
            saveBtn.prop("disabled", true).attr("title", "Email sent - editing locked");
            addBtn.prop("disabled", true).attr("title", "Email sent - locked");
        } else {
            const hasFilledProposals = filledProposals.length > 0;
            const hasAuctionEmail = auctionEmailInput.val().trim() !== '';

            if (!hasAuctionEmail) {
                saveBtn.prop("disabled", true);
            } else if (hasFilledProposals && unsavedChanges) {
                saveBtn.prop("disabled", false);
            } else if (unsavedChanges && hasAuctionEmail) {
                saveBtn.prop("disabled", false);
            } else {
                saveBtn.prop("disabled", true);
            }
        }

        const savedCount = container.find('[data-saved="true"]').length;
        const unlockedCount = container.find('[data-locked="false"]').length;
        const canSend = !emailStatus || emailStatus.can_send;

        if (savedCount === 0) {
            emailBtn.prop("disabled", true).attr("title", "Save proposals first");
        } else if (unlockedCount > 0) {
            emailBtn.prop("disabled", true).attr("title", "Save changes first");
        } else if (!canSend) {
            emailBtn.prop("disabled", true).attr("title", "Email already sent");
        } else {
            emailBtn.prop("disabled", false).attr("title", "Send proposals to customer");
        }
    }

    // create proposal DOM block
    function buildProposalHTML(index, uid = '', serviceProvider = '', price = '', details = '', locked = false, saved = false) {
        const lockedAttr = locked ? 'locked' : '';
        const readonlyAttr = locked ? 'readonly' : '';
        const disabledAttr = locked ? 'disabled' : '';
        const dataSaved = saved ? ' data-saved="true"' : '';
        const dataLocked = locked ? ' data-locked="true"' : ' data-locked="false"';
        const editorId = 'aees-details-' + index;
        const serviceProviders = aeesData.service_providers || [];

        // Build service provider options
        let providerOptions = '<option value="">-- Select Service Provider --</option>';
        serviceProviders.forEach((provider, idx) => {
            const selected = idx == serviceProvider ? 'selected' : '';
            const providerName = escapeHtml(provider.name || '');
            const providerImage = escapeHtml(provider.image || '');
            providerOptions += `<option value="${idx}" data-name="${providerName}" data-image="${providerImage}" ${selected}>${providerName}</option>`;
        });

        const selectStyle = locked
            ? 'width: 100%; height: 44px; border-radius: 8px; padding: 7px 15px; background-color: #f0f0f1; cursor: not-allowed;'
            : 'width: 100%; height: 44px; border-radius: 8px; padding: 7px 15px;';

        const noProvidersWarning = serviceProviders.length === 0
            ? `<p class="description" style="color: #dc3545; margin-top: 8px; font-size: 12px;">
                <strong>No service providers configured.</strong> Please add service providers in Settings first.
               </p>`
            : '';

        return `
            <div class="aees-proposal-card ${lockedAttr}" ${dataLocked} ${dataSaved} data-uid="${uid}">
                <div class="aees-proposal-header">
                    <h3>Proposal #${index + 1}</h3>
                    <div class="aees-proposal-actions">
                        <button type="button" class="button aees-edit-proposal">${locked ? '✏️ Edit' : '💾 Save'}</button>
                        <button type="button" class="button button-link-delete aees-remove-proposal">🗑️ Delete</button>
                    </div>
                </div>
                <div class="aees-proposal-body">
                    <div class="aees-field-group">
                        <label>Service Provider <span style="color: #dc3545; font-weight: 700;">*</span></label>
                        <select name="proposals[${index}][service_provider]" class="aees-service-provider-select" ${disabledAttr} style="${selectStyle}">
                            ${providerOptions}
                        </select>
                        ${noProvidersWarning}
                    </div>
                    <div class="aees-field-group">
                        <label>Price</label>
                        <div class="aees-price-input-wrapper">
                            <span class="aees-price-prefix">$</span>
                            <input type="text" name="proposals[${index}][price]" class="aees-price-input" value="${escapeHtml(price)}" ${readonlyAttr} placeholder="0.00" pattern="[0-9]*\.?[0-9]*" />
                        </div>
                    </div>
                    <div class="aees-field-group full">
                        <label>Proposal Details</label>
                        <div class="aees-wysiwyg-wrapper" data-editor-id="${editorId}">
                            <textarea id="${editorId}" name="proposals[${index}][details]" class="aees-proposal-details" rows="8">${escapeHtml(details)}</textarea>
                        </div>
                    </div>
                    <input type="hidden" name="proposals[${index}][uid]" value="${escapeHtml(uid)}" />
                </div>
            </div>
        `;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // initial state: ensure saved proposals are marked data-saved (already set in template)
    setUnsaved(false);

    // Check if entry is readonly and hide send email button completely if rejected
    const isReadOnly = $(".aees-edit-entry-container").hasClass("aees-entry-readonly");
    if (isReadOnly) {
        emailBtn.hide(); // Hide the button completely when rejected
        addBtn.hide(); // Hide add proposal button as well
    }

    // Check if entry is closed and lock editing
    const isClosed = aeesData.entry_status === 'closed';
    if (isClosed && !isReadOnly) {
        // Lock all proposal edit/delete buttons
        container.find(".aees-edit-proposal, .aees-remove-proposal").prop("disabled", true).css({
            "opacity": "0.5",
            "cursor": "not-allowed",
            "pointer-events": "none"
        });

        // Lock auction house dropdown
        auctionHouseSelect.prop("disabled", true).css({
            "background": "#F3F4F6",
            "cursor": "not-allowed"
        });

        // Ensure all proposals are locked
        container.find(".aees-proposal-card").addClass("locked").attr("data-locked", "true");
        container.find(".aees-proposal-card input, .aees-proposal-card textarea").prop("readonly", true).css("pointer-events", "none");
        container.find(".aees-proposal-card select.aees-service-provider-select").prop("disabled", true).css({
            "background-color": "#f0f0f1",
            "cursor": "not-allowed"
        });
    }

    // Check if there are saved proposals (on page load)
    const hasSavedProposals = container.find('[data-saved="true"]').length > 0;
    const auctionEmailValue = auctionEmailInput.val();

    // Lock dropdown if there are saved proposals OR if auction email is already set
    if ((hasSavedProposals || auctionEmailValue) && !isReadOnly && !isClosed) {
        // Lock auction house dropdown if there are saved proposals
        auctionHouseSelect.prop("disabled", true).css({
            "background": "#F3F4F6",
            "cursor": "not-allowed"
        });
    }

    // Check if email is sent and not expired - disable editing capabilities
    const emailStatus = aeesData.email_status;
    const isEmailSent = emailStatus && emailStatus.is_sent && !emailStatus.is_expired;

    if (isEmailSent && !isReadOnly && !isClosed) {
        saveBtn.prop("disabled", true).attr("title", "Email sent - editing locked");
        addBtn.prop("disabled", true).attr("title", "Email sent - locked");

        container.find(".aees-edit-proposal, .aees-remove-proposal").prop("disabled", true).css({
            "opacity": "0.5",
            "cursor": "not-allowed",
            "pointer-events": "none"
        });

        auctionHouseSelect.prop("disabled", true).css({
            "background": "#F3F4F6",
            "cursor": "not-allowed"
        });
    }

    // Initialize WordPress editor for a textarea
    function initWPEditor(editorId) {
        if (typeof wp !== 'undefined' && wp.editor) {
            wp.editor.initialize(editorId, {
                tinymce: {
                    wpautop: true,
                    plugins: 'lists,link,textcolor,wordpress',
                    toolbar1: 'formatselect,bold,italic,bullist,numlist,link,forecolor,undo,redo',
                    setup: function(editor) {
                        editor.on('change', function() {
                            editor.save();
                            setUnsaved(true);
                        });
                    }
                },
                quicktags: false,  // Disable Text/HTML tab
                mediaButtons: false
            });
        }
    }

    // Remove WordPress editor
    function removeWPEditor(editorId) {
        if (typeof wp !== 'undefined' && wp.editor) {
            wp.editor.remove(editorId);
        }
    }

    // Add new proposal
    addBtn.on("click", function (e) {
        e.preventDefault();
        // Calculate the next proposal index based on current number of proposals
        const proposalIndex = container.find(".aees-proposal-card").length;
        const uid = "p_" + Math.random().toString(36).substring(2, 10);
        const html = buildProposalHTML(proposalIndex, uid, '', '', '', false, false);
        container.append(html);

        // Initialize WordPress editor for the new proposal
        const editorId = 'aees-details-' + proposalIndex;
        initWPEditor(editorId);

        setUnsaved(true);
        // focus first input
        container.find('.aees-proposal-card').last().find("input[name*='[title]']").focus();
    });

    // Remove proposal (frontend only -> mark unsaved)
    container.on("click", ".aees-remove-proposal", function () {
        $(this).closest(".aees-proposal-card").remove();
        setUnsaved(true);
    });

    container.on("click", ".aees-edit-proposal", function () {
        const card = $(this).closest(".aees-proposal-card");
        const isLocked = card.attr("data-locked") === "true";
        const editorId = card.find('.aees-wysiwyg-wrapper').data('editor-id');

        if (isLocked) {
            card.removeClass("locked");
            card.attr("data-locked", "false");
            card.find("input").prop("readonly", false).css("pointer-events", "auto");
            card.find("textarea").prop("readonly", false);
            card.find("select.aees-service-provider-select").prop("disabled", false).css({
                "background-color": "#ffffff",
                "cursor": "pointer"
            });
            $(this).text("💾 Save");

            if (editorId) {
                initWPEditor(editorId);
            }

            setUnsaved(true);
            updateButtonStates();
        } else {
            card.addClass("locked");
            card.attr("data-locked", "true");
            card.find("input").prop("readonly", true).css("pointer-events", "none");
            card.find("textarea").prop("readonly", true);
            card.find("select.aees-service-provider-select").prop("disabled", true).css({
                "background-color": "#f0f0f1",
                "cursor": "not-allowed"
            });
            $(this).text("✏️ Edit");

            if (editorId) {
                if (typeof tinymce !== 'undefined') {
                    const editor = tinymce.get(editorId);
                    if (editor) {
                        editor.save();
                    }
                }
                removeWPEditor(editorId);
            }

            setUnsaved(true);
        }
    });

    // When any input changes, mark unsaved
    container.on("input", "input, textarea", function () {
        setUnsaved(true);
    });

    // When service provider dropdown changes, mark unsaved
    container.on("change", "select.aees-service-provider-select", function () {
        setUnsaved(true);
    });

    // Real-time price input validation - only allow numbers and one decimal point
    container.on("input", ".aees-price-input", function () {
        let value = $(this).val();
        // Remove any non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Allow only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        // Limit to 2 decimal places
        if (parts.length === 2 && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        $(this).val(value);
        setUnsaved(true);
    });

    // Removed: Old auction email input validation handlers
    // Now using dropdown selection instead

    // Collect proposals from DOM (order may not matter)
    function collectProposals() {
        const arr = [];
        container.find(".aees-proposal-card").each(function () {
            const $c = $(this);
            const uid = $c.data('uid') || $c.find("input[type='hidden']").val() || '';
            const serviceProvider = $c.find("select[name*='[service_provider]']").val() || '';
            const price = $c.find("input[name*='[price]']").val() || '';

            // Get details from editor or textarea
            const editorId = $c.find('.aees-wysiwyg-wrapper').data('editor-id');
            let details = '';

            if (editorId && typeof tinymce !== 'undefined') {
                const editor = tinymce.get(editorId);
                if (editor) {
                    editor.save(); // Save to textarea
                }
            }

            details = $c.find("textarea[name*='[details]']").val() || '';

            arr.push({ uid: uid, service_provider: serviceProvider, price: price, details: details });
        });
        return arr;
    }

    // AJAX Save Entry
    saveBtn.on("click", function (e) {
        e.preventDefault();
        if (saveBtn.prop("disabled")) return;

        const proposals = collectProposals();
        const auctionEmail = auctionEmailInput.val().trim();

        // Validate auction house is selected
        if (!auctionEmail || auctionEmail === '') {
            Swal.fire({
                icon: 'error',
                title: 'Required Field',
                text: 'Please select an auction house from the dropdown.',
                confirmButtonColor: '#2271b1'
            });
            auctionHouseSelect.focus().css("border-color", "#dc3545");
            return;
        }

        // Email validation is handled by dropdown selection (already validated in settings)
        // No need for additional email format validation here

        // Check if there are any proposal cards on the page
        const proposalCardsCount = container.find(".aees-proposal-card").length;

        // Filter out completely empty proposals and validate non-empty ones
        let hasError = false;
        let errorMessage = '';
        const nonEmptyProposals = [];
        let emptyProposalCount = 0;

        proposals.forEach((p, index) => {
            const hasAnyContent = (p.service_provider && p.service_provider.trim()) ||
                                  (p.price && p.price.trim()) ||
                                  (p.details && p.details.trim());

            // Count empty proposals
            if (!hasAnyContent) {
                emptyProposalCount++;
                return; // Skip this proposal
            }

            // Validate proposals that have some content
            if (!p.service_provider || p.service_provider.trim() === '') {
                hasError = true;
                errorMessage = `Proposal #${index + 1}: Service provider is required`;
            } else if (!p.price || p.price.trim() === '') {
                hasError = true;
                errorMessage = `Proposal #${index + 1}: Price is required`;
            } else if (!isValidPrice(p.price)) {
                hasError = true;
                errorMessage = `Proposal #${index + 1}: Price must be a valid number (e.g., 100 or 100.50)`;
            } else if (!p.details || p.details.trim() === '') {
                hasError = true;
                errorMessage = `Proposal #${index + 1}: Details are required`;
            }

            // Only add non-empty proposals to the list
            if (!hasError) {
                nonEmptyProposals.push(p);
            }
        });

        if (hasError) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: errorMessage,
                confirmButtonColor: '#2271b1'
            });
            return;
        }

        // If user added proposal cards but they're all empty, show error
        if (proposalCardsCount > 0 && emptyProposalCount > 0 && nonEmptyProposals.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Proposals',
                text: 'You have added proposal forms but they are empty. Please fill in all required fields (Title, Price, and Details) or remove the empty proposals.',
                confirmButtonColor: '#2271b1'
            });
            return;
        }

        const payload = {
            action: 'aees_save_entry',
            nonce: aeesData.nonce,
            entry_id: aeesData.entry_id,
            auction_email: auctionEmailInput.val(),
            proposals: nonEmptyProposals  // Only send non-empty proposals
        };

        // UI: disable buttons while saving
        saveBtn.prop("disabled", true).text("Saving...");
        addBtn.prop("disabled", true);
        $.post(aeesData.ajax_url, payload)
            .done(function (res) {
                if (res.success) {
                    // First, remove completely empty proposal cards from DOM
                    container.find(".aees-proposal-card").each(function () {
                        const $c = $(this);
                        const serviceProvider = $c.find("select[name*='[service_provider]']").val()?.trim() || '';
                        const price = $c.find("input[name*='[price]']").val()?.trim() || '';
                        const details = $c.find("textarea[name*='[details]']").val()?.trim() || '';

                        // If completely empty, remove from DOM
                        if (!serviceProvider && !price && !details) {
                            const editorId = $c.find('.aees-wysiwyg-wrapper').data('editor-id');
                            if (editorId && typeof tinymce !== 'undefined') {
                                const editor = tinymce.get(editorId);
                                if (editor) {
                                    removeWPEditor(editorId);
                                }
                            }
                            $c.remove();
                        }
                    });

                    // Now mark remaining (non-empty) proposals as saved & locked
                    container.find(".aees-proposal-card").each(function (i) {
                        const $c = $(this);
                        const uid = payload.proposals[i] && payload.proposals[i].uid ? payload.proposals[i].uid : $c.data('uid') || '';
                        const editorId = $c.find('.aees-wysiwyg-wrapper').data('editor-id');

                        if (editorId && typeof tinymce !== 'undefined') {
                            const editor = tinymce.get(editorId);
                            if (editor) {
                                editor.save();
                                removeWPEditor(editorId);
                            }
                        }

                        $c.attr("data-saved", "true");
                        $c.attr("data-locked", "true");
                        $c.addClass("locked");
                        $c.find("input, textarea").prop("readonly", true).css("pointer-events", "none");
                        $c.find("select.aees-service-provider-select").prop("disabled", true).css({
                            "background-color": "#f0f0f1",
                            "cursor": "not-allowed"
                        });
                        $c.data('uid', uid);
                        $c.find(".aees-edit-proposal").text("✏️ Edit");
                        $c.find("input[type='hidden']").val(uid);

                        // Renumber proposal headers
                        $c.find(".aees-proposal-header h3").text("Proposal #" + (i + 1));
                    });

                    setUnsaved(false);

                    // Lock auction house dropdown after save
                    auctionHouseSelect.prop("disabled", true).css({
                        "background": "#F3F4F6",
                        "cursor": "not-allowed"
                    });

                    // enable email button if at least one saved proposal exists
                    const savedCount = container.find('[data-saved="true"]').length;
                    emailBtn.prop("disabled", savedCount === 0);

                    // toast
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Changes saved',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: res.data && res.data.message ? res.data.message : 'Save failed',
                        showConfirmButton: false,
                        timer: 2200
                    });
                    setUnsaved(true);
                }
            })
            .fail(function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'AJAX request failed',
                    showConfirmButton: false,
                    timer: 2200
                });
                setUnsaved(true);
            })
            .always(function () {
                saveBtn.text("💾 Update Entry");
                addBtn.prop("disabled", false);
                updateButtonStates(); // This will properly set the disabled state
            });
    });

    // Send Email
    emailBtn.on("click", function (e) {
        e.preventDefault();
        if ($(this).prop("disabled")) return;

        // Confirm before sending
        Swal.fire({
            title: 'Send Proposals?',
            text: 'This will email the proposals to the user.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2271b1',
            cancelButtonColor: '#999',
            confirmButtonText: 'Yes, Send Email',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button and show loading
                emailBtn.prop("disabled", true).text("Sending...");

                $.post(aeesData.ajax_url, {
                    action: 'aees_send_email',
                    nonce: aeesData.nonce,
                    entry_id: aeesData.entry_id
                })
                .done(function (res) {
                    if (res.success) {
                        if (res.data.email_status) {
                            aeesData.email_status = res.data.email_status;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Email Sent!',
                            html: (res.data.message || 'Proposals sent successfully') + '<br><br><strong>The page will reload to show email status.</strong>',
                            confirmButtonColor: '#2271b1',
                            timer: 2500,
                            timerProgressBar: true
                        }).then(() => {
                            // Reload page to show email status box
                            location.reload();
                        });
                    } else {
                        // Show detailed error message
                        const errorMessage = res.data && res.data.message ? res.data.message : 'Failed to send email';
                        const errorDetails = res.data && res.data.details ? '<br><br><small>' + res.data.details + '</small>' : '';

                        Swal.fire({
                            icon: 'error',
                            title: 'Cannot Send Email',
                            html: errorMessage + errorDetails,
                            confirmButtonColor: '#2271b1'
                        });
                        emailBtn.prop("disabled", false).text("✉️ Send Email");
                    }
                })
                .fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'AJAX request failed',
                        confirmButtonColor: '#2271b1'
                    });
                    emailBtn.prop("disabled", false).text("✉️ Send Email");
                });
            }
        });
    });

    // Refresh Cache Button
    $("#aees-refresh-cache").on("click", function (e) {
        e.preventDefault();
        const btn = $(this);
        const originalText = btn.html();

        // Disable button and show loading state
        btn.prop("disabled", true).html('<span class="dashicons dashicons-update aees-spin"></span> Refreshing...');

        $.ajax({
            url: aeesData.ajax_url,
            type: 'POST',
            data: {
                action: 'aees_refresh_cache',
                nonce: aeesData.nonce,
                entry_id: aeesData.entry_id
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Cache Cleared!',
                        text: 'Reloading page with fresh data...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload the page to show fresh data
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.data?.message || 'Failed to refresh cache'
                    });
                    btn.prop("disabled", false).html(originalText);
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Network error. Please try again.'
                });
                btn.prop("disabled", false).html(originalText);
            }
        });
    });

    // Ensure button states on load
    updateButtonStates();

    // Toggle Entry Status Button (Open/Close)
    $("#aees-toggle-entry-status").on("click", function (e) {
        e.preventDefault();
        const btn = $(this);
        const currentStatus = btn.data("current-status");
        const newStatus = currentStatus === 'closed' ? 'open' : 'closed';

        // Extra safety: Don't allow reopening if entry has authorized proposals
        if (newStatus === 'open' && aeesData.has_authorized_proposals) {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Reopen',
                text: 'This entry has authorized proposals and is permanently closed.',
                confirmButtonColor: '#2271b1'
            });
            return;
        }

        // Confirmation dialog
        const confirmTitle = newStatus === 'open'
            ? 'Reopen This Entry?'
            : 'Close This Entry?';
        const confirmText = newStatus === 'open'
            ? 'Reopening this entry will allow you to create new proposals and send new emails to the customer.'
            : 'Closing this entry will prevent creating new proposals until you reopen it.';

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2271b1',
            cancelButtonColor: '#999',
            confirmButtonText: newStatus === 'open' ? 'Yes, Reopen Entry' : 'Yes, Close Entry',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button and show loading
                const originalHTML = btn.html();
                btn.prop("disabled", true).html('<span class="dashicons dashicons-update aees-spin"></span> Processing...');

                $.ajax({
                    url: aeesData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aees_toggle_entry_status',
                        nonce: aeesData.nonce,
                        entry_id: aeesData.entry_id,
                        status: newStatus
                    },
                    success: function (response) {
                        if (response.success) {
                            // Update button state
                            btn.data("current-status", newStatus);

                            // Update button appearance
                            if (newStatus === 'open') {
                                btn.removeClass('button-primary').addClass('button-secondary');
                                btn.find('.dashicons').removeClass('dashicons-unlock').addClass('dashicons-lock');
                                btn.html('<span class="dashicons dashicons-lock" style="margin-top: 3px;"></span> Close Entry');
                                btn.attr('title', 'Click to close this entry');
                            } else {
                                btn.removeClass('button-secondary').addClass('button-primary');
                                btn.find('.dashicons').removeClass('dashicons-lock').addClass('dashicons-unlock');
                                btn.html('<span class="dashicons dashicons-unlock" style="margin-top: 3px;"></span> Reopen Entry');
                                btn.attr('title', 'Click to reopen this entry');
                            }

                            // Update status badge
                            const badge = $('#aees-entry-status-badge');
                            if (newStatus === 'closed') {
                                badge.removeClass('aees-badge-info').addClass('aees-badge-danger');
                                badge.html('🔒 Entry Closed');
                            } else {
                                badge.removeClass('aees-badge-danger').addClass('aees-badge-info');
                                badge.html('🔓 Entry Open');
                            }

                            // Update global status for later use
                            aeesData.entry_status = newStatus;

                            // Update button states
                            updateButtonStates();

                            // Always reload page to show correct notice from PHP template
                            const shouldReload = true;

                            Swal.fire({
                                icon: 'success',
                                title: newStatus === 'open' ? 'Entry Reopened!' : 'Entry Closed!',
                                text: response.data.message || 'Entry status updated successfully',
                                confirmButtonColor: '#2271b1',
                                timer: shouldReload ? 1500 : 2000,
                                timerProgressBar: true
                            }).then(() => {
                                if (shouldReload) {
                                    // Reload page to show cleared proposals
                                    location.reload();
                                }
                            });

                            if (!shouldReload) {
                                btn.prop("disabled", false);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.data?.message || 'Failed to update entry status'
                            });
                            btn.prop("disabled", false).html(originalHTML);
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Network error. Please try again.'
                        });
                        btn.prop("disabled", false).html(originalHTML);
                    }
                });
            }
        });
    });

    // Collapsible Form Data functionality
    const collapsibleHeader = $(".aees-collapsible-header");

    collapsibleHeader.on("click", function() {
        const collapsible = $(this).closest(".aees-collapsible");
        const content = collapsible.find(".aees-collapsible-content");
        const icon = $(this).find(".aees-collapsible-icon");

        if (content.hasClass("open")) {
            // Close it
            content.removeClass("open").slideUp(300);
            icon.text("+");
        } else {
            // Open it
            content.addClass("open").slideDown(300);
            icon.text("−");
        }
    });

});
