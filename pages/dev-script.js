(function() {
    let devtoolsDetected = false;

    function showSecurityMessage(message) {
        let securityBanner = document.getElementById('security-banner');
        if (!securityBanner) {
            securityBanner = document.createElement('div');
            securityBanner.id = 'security-banner';
            securityBanner.style.position = 'fixed';
            securityBanner.style.top = '0';
            securityBanner.style.left = '50%';
            securityBanner.style.transform = 'translateX(-50%)';
            securityBanner.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            securityBanner.style.color = 'white';
            securityBanner.style.padding = '10px 20px';
            securityBanner.style.borderRadius = '0 0 10px 10px';
            securityBanner.style.fontSize = '18px';
            securityBanner.style.zIndex = '9999';
            securityBanner.style.textAlign = 'center';
            document.body.appendChild(securityBanner);
        }
        securityBanner.innerText = message;
        setTimeout(() => securityBanner.remove(), 5000);
    }

    function blockPage() {
        if (!devtoolsDetected) {
            devtoolsDetected = true;
            document.documentElement.innerHTML = `
                <h2 style="color:red; text-align:center;">Security Alert!</h2>
                <p style="text-align:center;">DevTools detected. Access denied!</p>
            `;
            setTimeout(() => {
                window.location.href = '/404.html';
            }, 3000);
            throw new Error('DevTools detected, execution halted');
        }
    }

    function detectDevTools() {
        const threshold = 160;
        if ((window.outerWidth - window.innerWidth > threshold) || (window.outerHeight - window.innerHeight > threshold)) {
            blockPage();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.keyCode === 123 || (event.ctrlKey && event.shiftKey && [73, 74].includes(event.keyCode)) || (event.ctrlKey && event.keyCode === 85)) {
            event.preventDefault();
            showSecurityMessage('UpiGateway™ Security Activated: DevTools are disabled!');
        }
    });

    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
        showSecurityMessage('Security Warning: Right-click is disabled!');
    });

    function detectConsole() {
        let before = new Date().getTime();
        debugger;
        let after = new Date().getTime();
        if (after - before > 200) blockPage();
    }

    function detectDebugger() {
        let start = performance.now();
        for (let i = 0; i < 1000; i++);
        let duration = performance.now() - start;
        if (duration > 200) blockPage();
    }

    if (!devtoolsDetected) {
        window.addEventListener('resize', detectDevTools);
        setInterval(detectConsole, 3000);
        setInterval(detectDebugger, 3000);
    }
})();
