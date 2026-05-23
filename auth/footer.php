<!-- Footer -->
    <footer class="fixed bottom-4 left-1/2 transform -translate-x-1/2 opacity-40 hover:opacity-100 transition-opacity duration-300 z-10">
        <div class="text-center text-sm text-gray-600">
            <span id="currentYear"></span> © <span class="font-bold text-indigo-600">UpiGateway™</span> - All rights reserved.
        </div>
    </footer>

    <!-- To-Top Button -->
    <div class="fixed right-56 bottom-0 z-20 bg-white p-3 rounded-full border-2 border-gray-800 shadow-lg opacity-40 hover:opacity-100 hover:bg-gray-100 transition-all duration-300 cursor-pointer" onclick="scrollToTop()">
        <i class="bi bi-chevron-double-up"></i>
    </div>

    <!-- JavaScript -->
    <script>
        document.getElementById("currentYear").textContent = new Date().getFullYear();

        function scrollToTop() {
            let currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
            if (currentScroll > 0) {
                window.requestAnimationFrame(scrollToTop);
                window.scrollTo(0, currentScroll - currentScroll / 10); // Smooth scroll
            }
        }
    </script>