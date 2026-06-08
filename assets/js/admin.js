/**
 * Gutenberg Utility Classes – Admin JS
 * Version: 1.0.0
 *
 * Handles live search, tab switching, and clipboard copy.
 * Pure Vanilla JS, no jQuery dependency.
 */

'use strict';

(function () {

    var searchInput = document.getElementById('guc-search');
    var tabs        = document.querySelectorAll('.nav-tab[data-tab]');
    var panels      = document.querySelectorAll('.guc-panel');
    var copyBtns    = document.querySelectorAll('.guc-copy-btn');

    // -------------------------------------------------------------------------
    // Tab switching
    // -------------------------------------------------------------------------

    /**
     * Activates a tab by its identifier and shows only the matching panel.
     * Has no effect on panel visibility while a search term is active.
     *
     * @param {string} targetId  The data-tab value to activate.
     */
    function activateTab(targetId) {
        tabs.forEach(function (tab) {
            tab.classList.toggle('nav-tab-active', tab.dataset.tab === targetId);
        });

        // Only control panel visibility when not searching.
        if (searchInput && searchInput.value.trim() !== '') {
            return;
        }

        panels.forEach(function (panel) {
            panel.hidden = panel.id !== 'guc-panel-' + targetId;
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            activateTab(this.dataset.tab);
        });
    });

    // -------------------------------------------------------------------------
    // Live search
    // -------------------------------------------------------------------------

    /**
     * Filters all table rows across all panels based on a search term.
     * When the term is empty the view returns to single-tab mode.
     *
     * @param {string} term  Lowercase search string.
     */
    function filterRows(term) {
        var isSearching = term.length > 0;

        panels.forEach(function (panel) {
            // While searching, all panels are visible so results span all tabs.
            panel.hidden = false;

            var rows      = panel.querySelectorAll('tbody tr.guc-row');
            var noResults = panel.querySelector('.guc-no-results');
            var visible   = 0;

            rows.forEach(function (row) {
                var match = !isSearching || row.textContent.toLowerCase().indexOf(term) !== -1;
                row.hidden = !match;
                if (match) {
                    visible++;
                }
            });

            if (noResults) {
                noResults.hidden = visible > 0;
            }
        });

        // Search cleared: restore single-tab view for the active tab.
        if (!isSearching) {
            var activeTab    = document.querySelector('.nav-tab.nav-tab-active');
            var activeTarget = activeTab ? activeTab.dataset.tab : null;

            panels.forEach(function (panel) {
                panel.hidden = activeTarget
                    ? panel.id !== 'guc-panel-' + activeTarget
                    : false;
            });
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterRows(this.value.trim().toLowerCase());
        });
    }

    // -------------------------------------------------------------------------
    // Clipboard copy
    // -------------------------------------------------------------------------

    copyBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var className = this.dataset['class'] || '';

            if (!className) {
                return;
            }

            if (!navigator.clipboard) {
                // Clipboard API unavailable (non-secure context).
                btn.textContent = '✗';
                setTimeout(function () { btn.textContent = '📋'; }, 1500);
                return;
            }

            var self = this;

            navigator.clipboard.writeText(className).then(function () {
                self.textContent = '✓';
                setTimeout(function () { self.textContent = '📋'; }, 1500);
            }).catch(function () {
                self.textContent = '✗';
                setTimeout(function () { self.textContent = '📋'; }, 1500);
            });
        });
    });

})();
