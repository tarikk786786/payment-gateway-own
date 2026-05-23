(function() {
    let devtoolsDetected = false;

    function blockPage() {
        if (!devtoolsDetected) {
            devtoolsDetected = true;
            document.documentElement.innerHTML = `
                <h2 style="color:red; text-align:center;">Security Alert!</h2>
                <p style="text-align:center;">DevTools detected. Access denied!</p>
            `;
            setTimeout(() => {
                window.location.href = '/404';
            }, 3000);
            throw new Error('DevTools detected, execution halted');
        }
    }

    function detectDevTools() {
        const widthThreshold = window.outerWidth - window.innerWidth > 160;
        const heightThreshold = window.outerHeight - window.innerHeight > 160;
        if (widthThreshold || heightThreshold) {
            blockPage();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.keyCode === 123 || (event.ctrlKey && event.shiftKey && [73, 74].includes(event.keyCode)) || (event.ctrlKey && event.keyCode === 85)) {
            event.preventDefault();
            alert('Security Warning: DevTools are disabled!');
        }
    });

    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
        alert('Security Warning: Right-click is disabled!');
    });

    function detectConsole() {
        let start = performance.now();
        for (let i = 0; i < 1000; i++);
        let duration = performance.now() - start;
        if (duration > 50) blockPage();
    }

    function detectDebugger() {
        let before = new Date().getTime();
        debugger;
        let after = new Date().getTime();
        if (after - before > 100) blockPage();
    }

    if (!devtoolsDetected) {
        window.addEventListener('resize', detectDevTools);
        setInterval(detectConsole, 2000);
        setInterval(detectDebugger, 1000);
        setInterval(detectDevTools, 500);

        const trap = /./;
        Object.defineProperty(trap, 'toString', {
            value: () => blockPage()
        });
    }
})();
