// Tab persistence for settings page
document.addEventListener('DOMContentLoaded', function() {
    const settingsTabs = document.getElementById('settingsTabs');

    // Function to get current tab
    function getCurrentTab() {
        const activeTab = settingsTabs.querySelector('.nav-link.active');
        return activeTab ? activeTab.getAttribute('href').substring(1) : 'departments';
    }

    // Function to activate tab
    function activateTab(tabId) {
        const tabLink = settingsTabs.querySelector(`a[href="#${tabId}"]`);
        if (tabLink) {
            // Remove active class from all tabs
            settingsTabs.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            // Add active class to target tab
            tabLink.classList.add('active');

            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active', 'in');
            });

            // Show target tab pane
            const targetPane = document.getElementById(tabId);
            if (targetPane) {
                targetPane.classList.add('active', 'in');
            }
        }
    }

    // Store current tab when changed
    settingsTabs.addEventListener('click', function(e) {
        if (e.target.classList.contains('nav-link')) {
            const tabId = e.target.getAttribute('href').substring(1);
            sessionStorage.setItem('cnxevents_settings_active_tab', tabId);
        }
    });

    // Check for activeTab from server (set by controller) or stored tab
    const container = document.querySelector('.container[data-active-tab]');
    const serverActiveTab = container ? container.getAttribute('data-active-tab') : null;
    const storedTab = sessionStorage.getItem('cnxevents_settings_active_tab');

    if (serverActiveTab && serverActiveTab !== 'departments') {
        // Activate tab from server (after redirect)
        activateTab(serverActiveTab);
        // Clear the session active_tab since we've used it
        sessionStorage.removeItem('cnxevents_settings_active_tab');
    } else if (storedTab && storedTab !== 'departments') {
        // Activate stored tab
        activateTab(storedTab);
    }

    // Add current tab to all forms
    document.querySelectorAll('form').forEach(form => {
        const currentTab = getCurrentTab();
        let tabInput = form.querySelector('input[name="active_tab"]');
        if (!tabInput) {
            tabInput = document.createElement('input');
            tabInput.type = 'hidden';
            tabInput.name = 'active_tab';
            form.appendChild(tabInput);
        }
        tabInput.value = currentTab;
    });
});