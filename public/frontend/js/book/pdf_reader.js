// Đặt worker path cho PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

// Đặt hàm toggleReadingStats để có thể truy cập toàn cục (từ HTML)
window.toggleReadingStats = function() {
    const stats = document.getElementById('reading-stats');
    if (stats) {
        stats.classList.toggle('minimized');
    } else {
        console.log("Không tìm thấy phần tử reading-stats");
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // Nếu không có PDF nào để hiển thị, dừng lại
    if (!currentPdfUrl) {
        const loadingIndicator = document.getElementById('loading-indicator');
        loadingIndicator.innerHTML = '<p class="text-danger">Không tìm thấy tài liệu PDF cho sách này.</p>';
        return;
    }

    // Các tham chiếu đến các phần tử DOM
    const pdfContent = document.getElementById('pdf-content');
    const loadingIndicator = document.getElementById('loading-indicator');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');
    const zoomInBtn = document.getElementById('zoom-in');
    const zoomOutBtn = document.getElementById('zoom-out');
    const zoomLevelSpan = document.getElementById('zoom-info');
    const settingsBtn = document.getElementById('settings-btn');
    const settingsContent = document.getElementById('settings-content');
    const bookmarkBtn = document.getElementById('bookmark-btn');
    const statsToggleBtn = document.getElementById('stats-toggle');

    // Biến để theo dõi thời gian đọc sách
    const bookId = document.querySelector('input[name="book_id"]')?.value || document.querySelector('.reader-container')?.getAttribute('data-book-id') || 0;
    console.log("Book ID:", bookId); // Debug
    let isActive = true;
    let readingInterval = null;
    const updateInterval = 30000; // Gửi dữ liệu mỗi 30 giây
    let lastPointsEarned = 0;

    let currentPdf = null;
    let currentPage = 1;
    let currentScale = 0.75; // Tỉ lệ zoom mặc định giảm xuống 75%
    let isRendering = false;
    let pageNumPending = null;

    // Tìm biến pagesPerView và thay đổi từ 1 thành 5
    const pagesPerView = 5;

    // Tải PDF
    pdfjsLib.getDocument(currentPdfUrl).promise.then(pdf => {
        currentPdf = pdf;
        totalPagesSpan.textContent = pdf.numPages;

        // Kích hoạt các nút điều khiển
        prevPageBtn.disabled = false;
        nextPageBtn.disabled = false;
        zoomInBtn.disabled = false;
        zoomOutBtn.disabled = false;

        // Hiển thị trang đầu tiên
        renderPages(1);

        // Kích hoạt nút chuyển trang - version mới với xử lý rõ ràng
        nextPageBtn.onclick = function () {
            console.log('Next page clicked, current page:', currentPage, 'total pages:', pdf.numPages);
            if (currentPage + pagesPerView <= pdf.numPages) {
                currentPage += pagesPerView;
                queueRenderPages(currentPage);
            } else if (currentPage < pdf.numPages) {
                // Trường hợp còn lại ít hơn pagesPerView trang
                currentPage = Math.max(1, pdf.numPages - pagesPerView + 1);
                queueRenderPages(currentPage);
            } else {
                // Khi đã ở trang cuối, quay lại trang đầu tiên
                currentPage = 1;
                queueRenderPages(currentPage);
            }
        };

        prevPageBtn.onclick = function () {
            console.log('Previous page clicked, current page:', currentPage);
            if (currentPage > pagesPerView) {
                currentPage -= pagesPerView;
                queueRenderPages(currentPage);
            } else if (currentPage > 1) {
                currentPage = 1;
                queueRenderPages(currentPage);
            }
        };

        // Xử lý phóng to thu nhỏ - version mới đảm bảo hoạt động
        zoomInBtn.onclick = function () {
            console.log('Zoom in clicked, current scale:', currentScale);
            if (currentScale < 3.0) {
                currentScale += 0.05; // Zoom tăng 5% thay vì 25%
                updateZoomLevel();
                queueRenderPages(currentPage);
                showStatus(`Phóng to ${Math.round(currentScale * 100)}%`, 'search-plus');
            }
        };

        zoomOutBtn.onclick = function () {
            console.log('Zoom out clicked, current scale:', currentScale);
            if (currentScale > 0.3) { // Mức tối thiểu 30%
                currentScale -= 0.05; // Zoom giảm 5% thay vì 25%
                updateZoomLevel();
                queueRenderPages(currentPage);
                showStatus(`Thu nhỏ ${Math.round(currentScale * 100)}%`, 'search-minus');
            }
        };

        // Bắt sự kiện phím
        document.addEventListener('keydown', (e) => {
            if (e.code === 'ArrowLeft' && currentPage > 1) {
                if (currentPage > pagesPerView) {
                    currentPage -= pagesPerView;
                } else {
                    currentPage = 1;
                }
                queueRenderPages(currentPage);
            } else if (e.code === 'ArrowRight' && currentPage < pdf.numPages) {
                if (currentPage + pagesPerView <= pdf.numPages) {
                    currentPage += pagesPerView;
                } else {
                    currentPage = Math.max(1, pdf.numPages - pagesPerView + 1);
                }
                queueRenderPages(currentPage);
            }
        });

        // Xử lý sự kiện resize window
        window.addEventListener('resize', debounce(() => {
            if (currentPage && currentPdf) {
                queueRenderPages(currentPage);
            }
        }, 250));

    }).catch(error => {
        console.error('Lỗi khi tải PDF:', error);
        loadingIndicator.innerHTML = `<p class="text-danger">Lỗi khi tải PDF: ${error.message}</p>`;
    });

    // Hàm cập nhật hiển thị mức zoom
    function updateZoomLevel() {
        zoomLevelSpan.textContent = `${Math.round(currentScale * 100)}%`;
    }

    // Hàm xếp hàng đợi render trang
    function queueRenderPages(startPage, callback) {
        if (isRendering) {
            pageNumPending = startPage;
        } else {
            renderPages(startPage, callback);
        }
    }

    // Hàm render các trang PDF
    async function renderPages(startPage, callback) {
        isRendering = true;

        // Cập nhật UI
        currentPageSpan.textContent = startPage + " - " + Math.min(startPage + pagesPerView - 1, currentPdf.numPages);
        prevPageBtn.disabled = startPage === 1;
        nextPageBtn.disabled = startPage + pagesPerView - 1 >= currentPdf.numPages;

        // Hiển thị loading
        loadingIndicator.style.display = 'block';

        // Xóa nội dung hiện tại
        pdfContent.innerHTML = '';

        // Cuộn lên đầu trang khi chuyển trang với hiệu ứng mượt mà
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        // Số trang cần hiển thị (không vượt quá tổng số trang)
        const pagesToRender = Math.min(pagesPerView, currentPdf.numPages - startPage + 1);

        const renderPromises = [];
        let renderedCount = 0;

        try {
            // Tạo và render từng trang
            for (let i = 0; i < pagesToRender; i++) {
                const pageNumber = startPage + i;

                // Tạo container cho trang hiện tại
                const pageContainer = document.createElement('div');
                pageContainer.className = 'pdf-page page-transition';
                pageContainer.setAttribute('data-page-number', pageNumber);
                pageContainer.style.opacity = '0';
                pageContainer.style.transition = 'opacity 0.3s ease';

                // Thêm placeholder hiển thị số trang
                const pageNumberElem = document.createElement('div');
                pageNumberElem.className = 'page-number';
                pageNumberElem.textContent = pageNumber;
                pageContainer.appendChild(pageNumberElem);

                // Hiển thị loading cho trang
                const loadingElem = document.createElement('div');
                loadingElem.className = 'loading-indicator page-loading';
                loadingElem.innerHTML = '<div class="loading-spinner"></div><p>Đang tải trang ' + pageNumber + '...</p>';
                pageContainer.appendChild(loadingElem);

                pdfContent.appendChild(pageContainer);

                // Thêm promise cho việc render trang
                renderPromises.push(
                    renderSinglePage(pageNumber, pageContainer)
                        .then(() => {
                            renderedCount++;
                            // Hiển thị trang với hiệu ứng fade in
                            requestAnimationFrame(() => {
                                pageContainer.style.opacity = '1';
                            });
                        })
                        .catch(error => {
                            console.error(`Lỗi khi render trang ${pageNumber}:`, error);
                        })
                );
            }

            // Đợi tất cả các trang được render xong
            await Promise.all(renderPromises);

            // Ẩn loading indicator
            loadingIndicator.style.display = 'none';

            // Gọi callback nếu có
            if (typeof callback === 'function') {
                callback();
            }
        } catch (error) {
            console.error('Lỗi khi render các trang:', error);
            loadingIndicator.innerHTML = `<p class="text-danger">Lỗi khi render trang: ${error.message}</p>`;
            loadingIndicator.style.display = 'block';
        } finally {
            // Luôn đánh dấu render hoàn tất kể cả có lỗi
            isRendering = false;

            // Kiểm tra xem có trang nào trong hàng đợi không
            if (pageNumPending !== null) {
                const pendingPage = pageNumPending;
                pageNumPending = null;
                setTimeout(() => renderPages(pendingPage), 100);
            }
        }
    }

    // Hàm render một trang đơn
    async function renderSinglePage(pageNumber, existingContainer = null) {
        try {
            // Lấy trang PDF
            const page = await currentPdf.getPage(pageNumber);

            // Nếu đã có container, sử dụng nó
            let pageContainer = existingContainer;

            // Tính toán kích thước viewport để phù hợp với chiều rộng container
            const containerWidth = pdfContent.clientWidth;
            const originalViewport = page.getViewport({ scale: 1.0 });

            // Tính toán tỉ lệ để vừa với container
            const containerScale = containerWidth / originalViewport.width;
            const finalScale = containerScale * currentScale;

            // Tạo viewport với tỉ lệ mới
            const viewport = page.getViewport({ scale: finalScale });

            // Thêm kích thước cho container
            pageContainer.style.width = `${viewport.width}px`;
            pageContainer.style.height = `${viewport.height}px`;

            // Tạo canvas cho trang
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-canvas';
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            // Thêm canvas vào container trang
            pageContainer.appendChild(canvas);

            // Tạo div cho text layer nếu chưa có
            let textLayerDiv = pageContainer.querySelector('.pdf-textLayer');
            if (!textLayerDiv) {
                textLayerDiv = document.createElement('div');
                textLayerDiv.className = 'pdf-textLayer';
                textLayerDiv.style.width = `${viewport.width}px`;
                textLayerDiv.style.height = `${viewport.height}px`;
                pageContainer.appendChild(textLayerDiv);
            }

            // Xóa các phần tử loading nếu có
            const loading = pageContainer.querySelector('.page-loading');
            if (loading) {
                loading.remove();
            }

            // Render trang vào canvas
            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            await page.render(renderContext).promise;

            // Lấy nội dung text và render text layer
            const textContent = await page.getTextContent();

            // Sử dụng text layer builder từ PDF.js với tham số cập nhật
            await pdfjsLib.renderTextLayer({
                textContentSource: textContent,
                container: textLayerDiv,
                viewport: viewport,
                textDivs: []
            }).promise;

            // Đánh dấu trang đã được render
            pageContainer.setAttribute('data-rendered', 'true');

            return true;
        } catch (error) {
            console.error(`Lỗi khi render trang ${pageNumber}:`, error);
            throw error;
        }
    }

    // Function để delay việc thực thi callback (tránh gọi quá nhiều khi resize)
    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }

    // Toggle settings dropdown
    settingsBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (settingsContent.style.display === 'none' || !settingsContent.style.display) {
            settingsContent.style.display = 'block';
        } else {
            settingsContent.style.display = 'none';
        }
    });

    // Đóng settings dropdown khi click bên ngoài
    document.addEventListener('click', function (e) {
        if (!settingsBtn.contains(e.target) && !settingsContent.contains(e.target)) {
            settingsContent.style.display = 'none';
        }
    });

    // Toggle bookmark
    bookmarkBtn.addEventListener('click', function () {
        // Kiểm tra đăng nhập trước khi thực hiện
        const isLoggedIn = document.body.getAttribute('data-user-logged-in') === '1';
        if (!isLoggedIn) {
            window.location.href = "/login";
            return;
        }

        this.classList.toggle('active');
        const icon = this.querySelector('i');
        if (icon.classList.contains('far')) {
            icon.classList.replace('far', 'fas');
            // Hiển thị thông báo
            showPointAlert('Đã thêm vào yêu thích');

            // Gọi API để lưu bookmark
            fetch('/front/book/bookmark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    item_id: bookId,
                    item_code: 'book'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Nếu có lỗi, đảo ngược trạng thái
                    icon.classList.replace('fas', 'far');
                    showPointAlert(data.msg || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                // Đảo ngược trạng thái nếu có lỗi
                icon.classList.replace('fas', 'far');
            });
        } else {
            icon.classList.replace('fas', 'far');
            // Hiển thị thông báo
            showPointAlert('Đã xóa khỏi yêu thích');

            // Gọi API để xóa bookmark
            fetch('/front/book/bookmark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    item_id: bookId,
                    item_code: 'book'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Nếu có lỗi, đảo ngược trạng thái
                    icon.classList.replace('far', 'fas');
                    showPointAlert(data.msg || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                // Đảo ngược trạng thái nếu có lỗi
                icon.classList.replace('far', 'fas');
            });
        }
    });

    // Toggle reading stats
    if (statsToggleBtn) {
        statsToggleBtn.addEventListener('click', function () {
            toggleReadingStats();
        });
    }

    // Xử lý hiển thị/ẩn UI khi cuộn trang
    const headerContainer = document.getElementById('header-container');
    const pdfControls = document.getElementById('pdf-controls');
    let isScrolling;
    let lastScrollDirection = null;
    let scrollTimeStamp = Date.now();
    const scrollDelay = 50; // Thời gian delay để giảm số lần xử lý sự kiện scroll

    // Thêm biến để theo dõi trạng thái hiển thị UI
    let isUIToggling = false;  // Biến để theo dõi trạng thái đang chuyển đổi UI
    const uiToggleDelay = 500; // Khoảng thời gian tối thiểu giữa các lần chuyển đổi UI
    let uiVisible = true;  // Biến để theo dõi trạng thái hiển thị/ẩn của UI

    // Cập nhật lại hàm forceShowUI để không bị toggle quá nhanh
    function forceShowUI() {
        if (!uiVisible && !isUIToggling) {
            isUIToggling = true;
            requestAnimationFrame(() => {
                headerContainer.classList.remove('hidden');
                pdfControls.classList.remove('hidden');
                document.body.classList.remove('hide-ui');
                uiVisible = true;

                // Đặt lại trạng thái sau khi chuyển đổi xong
                setTimeout(() => {
                    isUIToggling = false;
                }, uiToggleDelay);
            });
        }
    }

    function forceHideUI() {
        if (uiVisible && !isUIToggling) {
            isUIToggling = true;
            requestAnimationFrame(() => {
                headerContainer.classList.add('hidden');
                pdfControls.classList.add('hidden');
                document.body.classList.add('hide-ui');
                uiVisible = false;

                // Đặt lại trạng thái sau khi chuyển đổi xong
                setTimeout(() => {
                    isUIToggling = false;
                }, uiToggleDelay);
            });
        }
    }

    // Thêm những hàm bị mất
    function showUI() {
        forceShowUI();
    }

    function hideUI() {
        forceHideUI();
    }

    // Thêm event listener mạnh mẽ hơn cho scroll event
    let scrollTimer;

    window.addEventListener('wheel', function (e) {
        clearTimeout(scrollTimer);

        // Chỉ phản ứng với các cử chỉ cuộn lớn hơn một ngưỡng nhất định
        const scrollDetectionThreshold = 20; // Tăng lên để giảm độ nhạy
        const scrollThreshold = 30; // Ngưỡng cuộn nhỏ hơn để phản ứng nhanh hơn

        if (Math.abs(e.deltaY) < scrollDetectionThreshold) {
            // Bỏ qua các cử chỉ cuộn nhẹ
            return;
        }

        // Chỉ ẩn UI khi cuộn xuống, mức nhạy bình thường
        if (e.deltaY > scrollDetectionThreshold && window.scrollY > scrollThreshold) {
            forceHideUI();
        }
        // Chỉ hiện UI khi cuộn mạnh lên trên - yêu cầu cuộn mạnh hơn nhiều
        else if (e.deltaY < -scrollDetectionThreshold * 3) {
            forceShowUI();
        }

        // Không tự hiện UI khi dừng cuộn ở giữa trang, chỉ hiện khi gần đầu trang
        scrollTimer = setTimeout(() => {
            if (window.scrollY < scrollThreshold / 2) { // Chỉ hiện khi rất gần đầu trang
                forceShowUI();
            }
        }, 500); // Tăng thời gian timeout để không hiện quá nhanh
    }, { passive: true });

    // Đối với màn hình cảm ứng
    let touchStartY = 0;

    window.addEventListener('touchstart', function (e) {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    window.addEventListener('touchmove', function (e) {
        const touchY = e.touches[0].clientY;
        const diff = touchStartY - touchY;
        const scrollThreshold = 30; // Thêm biến scrollThreshold vào đây

        clearTimeout(scrollTimer);

        // Chỉ phản ứng khi vuốt đủ mạnh
        const touchDetectionThreshold = 15; // Ngưỡng phát hiện vuốt (giá trị lớn hơn = ít nhạy hơn)

        if (Math.abs(diff) < touchDetectionThreshold) {
            // Bỏ qua các cử chỉ vuốt nhẹ
            return;
        }

        if (diff > touchDetectionThreshold && window.scrollY > scrollThreshold) {
            // Vuốt lên mạnh (cuộn xuống)
            forceHideUI();
        } else if (diff < -touchDetectionThreshold * 2) {
            // Vuốt xuống mạnh (cuộn lên)
            // Yêu cầu vuốt mạnh hơn để hiện UI
            forceShowUI();
        }

        touchStartY = touchY;

        scrollTimer = setTimeout(() => {
            if (window.scrollY < scrollThreshold) {
                forceShowUI();
            }
        }, 300); // Tăng thời gian timeout
    }, { passive: true });

    // Hiển thị thông báo nhận điểm
    function showPointAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'point-alert';
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);

        // Tự động xóa thông báo sau 5 giây
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // Cập nhật giao diện thống kê thời gian đọc
    function updateReadingStatsUI(totalMinutes, pointsEarned) {
        // Cập nhật debug panel nếu tồn tại
        const debugPanel = document.getElementById('debug-panel');
        if (debugPanel) {
            document.getElementById('debug-time').textContent = totalMinutes.toFixed(1);
            document.getElementById('debug-points').textContent = pointsEarned;
            document.getElementById('debug-last-update').textContent = new Date().toLocaleTimeString();
            document.getElementById('debug-status').textContent = 'Active';
        }

        // Phát hiện nếu statsContainer không tồn tại, tạo mới
        let statsContainer = document.getElementById('reading-stats');
        
        if (!statsContainer) {
            // Create the stats container
            statsContainer = document.createElement('div');
            statsContainer.id = 'reading-stats';
            statsContainer.className = 'reading-stats';
            statsContainer.innerHTML = `
                <div class="reading-stats-title">
                    Thống kê đọc sách
                    <span class="reading-stats-close" onclick="toggleReadingStats()"></span>
                </div>
                <div class="reading-stats-item">
                    <span>Thời gian đọc:</span>
                    <span id="reading-time">0</span> phút
                </div>
                <div class="reading-stats-item">
                    <span>Điểm nhận được:</span>
                    <span id="points-earned">0</span> điểm
                </div>
            `;
            document.body.appendChild(statsContainer);
        }

        // Cập nhật thời gian đọc - kiểm tra phần tử tồn tại
        const readingTimeEl = document.getElementById('reading-time');
        if (readingTimeEl) {
            readingTimeEl.textContent = totalMinutes.toFixed(1);
        }

        // Kiểm tra phần tử điểm tồn tại
        const pointsEl = document.getElementById('points-earned');
        if (!pointsEl) return; // Nếu không tìm thấy, thoát khỏi hàm
        
        // Cập nhật điểm nhận được
        // Hiển thị hiệu ứng khi có điểm mới
        if (pointsEarned > lastPointsEarned) {
            const newPoints = pointsEarned - lastPointsEarned;

            // Thêm class để tạo hiệu ứng
            pointsEl.classList.add('points-increase');

            // Cập nhật giá trị điểm
            pointsEl.textContent = pointsEarned;

            // Hiển thị thông báo điểm thưởng
            showPointAlert(`+${newPoints} điểm thưởng! Tiếp tục đọc để nhận thêm.`);

            // Xóa class sau khi hiệu ứng kết thúc
            setTimeout(() => {
                pointsEl.classList.remove('points-increase');
            }, 600);

            lastPointsEarned = pointsEarned;
        } else {
            pointsEl.textContent = pointsEarned;
        }
    }

    // Hàm gửi thông tin về thời gian đọc sách lên server
    function updateReadingTime() {
        console.log("Gọi hàm updateReadingTime, isActive =", isActive);
        
        if (!isActive) {
            console.log("Không hoạt động, bỏ qua cập nhật thời gian");
            return;
        }

        // Kiểm tra đăng nhập trước khi gửi dữ liệu
        const isLoggedIn = document.body.getAttribute('data-user-logged-in') === '1';
        if (!isLoggedIn) {
            console.log("Chưa đăng nhập, bỏ qua cập nhật thời gian");
            return;
        }

        console.log("Gửi cập nhật thời gian đọc với bookId =", bookId);
        fetch('/books/update-reading-time', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                book_id: bookId,
                active: isActive
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Thời gian đọc được cập nhật:', data.total_minutes.toFixed(1), 'phút');
                    console.log('Điểm đã nhận:', data.points_earned);

                    // Cập nhật giao diện
                    updateReadingStatsUI(data.total_minutes, data.points_earned);
                } else {
                    console.error('Lỗi cập nhật thời gian:', data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi khi cập nhật thời gian đọc:', error);
            });
    }

    // Khi trang được tải, bắt đầu theo dõi thời gian đọc
    console.log("Khởi tạo hệ thống tính thời gian đọc sách");
    window.readingSessionEnded = false; // Đảm bảo bắt đầu với phiên mới

    // Kiểm tra đăng nhập
    const isLoggedIn = document.body.getAttribute('data-user-logged-in') === '1';
    console.log("User đăng nhập:", isLoggedIn);
    
    if (isLoggedIn) {
        // Khởi tạo phiên đọc sách khi vào trang - chỉ khi đã đăng nhập
        console.log("Bắt đầu khởi tạo phiên đọc với book ID:", bookId);
        
        setTimeout(function () {
            fetch('/books/start-reading', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    book_id: bookId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Đã bắt đầu phiên đọc sách mới');
                        console.log('Phiên hiện tại:', data.session_id);

                        // Lưu ID phiên để dùng khi kết thúc
                        window.currentReadingSessionId = data.session_id;

                        // Bắt đầu theo dõi thời gian đọc
                        if (readingInterval) {
                            clearInterval(readingInterval);
                        }
                        
                        readingInterval = setInterval(function() {
                            updateReadingTime();
                        }, updateInterval);
                        console.log("Đã thiết lập interval cập nhật thời gian mỗi", updateInterval/1000, "giây");

                        // Cập nhật lần đầu ngay khi phiên được tạo
                        setTimeout(updateReadingTime, 1000);
                    } else {
                        console.error('Khởi tạo phiên đọc thất bại:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi khởi tạo phiên đọc:', error);
                });
        }, 1000);
    }

    // Theo dõi khi người dùng chuyển tab - chỉ dừng tính thời gian, không reset
    document.addEventListener('visibilitychange', function () {
        console.log("Visibility change:", document.hidden);
        isActive = !document.hidden;
        if (!window.readingSessionEnded) {
            updateReadingTime(); // Cập nhật ngay khi trạng thái thay đổi
        }
    });

    // Theo dõi khi user nhấn vào link nội bộ - sẽ reset session
    document.addEventListener('click', function (e) {
        // Kiểm tra xem có nhấp vào link nội bộ không
        const target = e.target.closest('a');
        if (target && target.href && !target.getAttribute('target')
            && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            // Chỉ reset khi chuyển trang nội bộ (không phải mở tab mới)
            // và không phải click chuột phải, tức là sẽ rời khỏi trang hiện tại
            console.log("Chuyển sang trang khác trong web, reset phiên đọc");
            finishReading();
        }
    });

    // Khi người dùng rời khỏi trang - reset phiên đọc
    window.addEventListener('beforeunload', function () {
        console.log("Thoát trang, reset phiên đọc");
        finishReading();
    });

    // Lưu thời điểm cuối cùng người dùng tương tác với trang
    let lastInteractionTime = Date.now();
    const interactionEvents = ['click', 'touchstart', 'mousemove', 'keydown', 'scroll'];

    // Theo dõi tương tác người dùng
    interactionEvents.forEach(event => {
        document.addEventListener(event, function () {
            if (!window.readingSessionEnded) {
                lastInteractionTime = Date.now();
                // Nếu trước đó đã không hoạt động, kích hoạt lại
                if (!isActive) {
                    console.log("Phát hiện tương tác, tiếp tục tính thời gian");
                    isActive = true;
                    updateReadingTime();
                }
            }
        }, { passive: true });
    });

    // Kiểm tra định kỳ nếu người dùng không tương tác trong thời gian dài (ví dụ: 5 phút)
    const inactivityCheckInterval = 60000; // kiểm tra mỗi phút
    const inactivityThreshold = 300000; // 5 phút

    setInterval(() => {
        if (!window.readingSessionEnded && isActive && Date.now() - lastInteractionTime > inactivityThreshold) {
            console.log("Không hoạt động trong 5 phút, dừng tính thời gian");
            isActive = false;
            updateReadingTime();
        }
    }, inactivityCheckInterval);

    /**
     * Kết thúc phiên đọc sách
     */
    function finishReading() {
        console.log("Kết thúc phiên đọc sách");

        // Nếu đã gọi finishReading rồi, không gọi lại nữa
        if (window.readingSessionEnded) {
            console.log("Phiên đọc đã kết thúc trước đó, bỏ qua");
            return;
        }

        console.log("⚠️ Kết thúc phiên đọc sách ⚠️");

        // Đánh dấu đã kết thúc phiên ngay lập tức để tránh gọi lại
        window.readingSessionEnded = true;

        // Xóa interval nếu còn tồn tại
        if (readingInterval) {
            clearInterval(readingInterval);
            readingInterval = null;
        }

        // Kiểm tra đăng nhập
        const isLoggedIn = document.body.getAttribute('data-user-logged-in') === '1';
        if (!isLoggedIn) return; // Không gửi dữ liệu nếu chưa đăng nhập

        try {
            // Thử cả hai phương pháp để đảm bảo dữ liệu được gửi đi

            // 1. Sử dụng sendBeacon (không đồng bộ nhưng đáng tin cậy khi đóng trang)
            const beaconSent = navigator.sendBeacon(
                '/books/finish-reading',
                JSON.stringify({
                    book_id: bookId,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                })
            );

            console.log("SendBeacon thành công:", beaconSent);

            // 2. Dự phòng: gửi fetch với keepalive nếu sendBeacon không thành công
            if (!beaconSent) {
                console.log("SendBeacon thất bại, thử phương pháp fetch");

                fetch('/books/finish-reading', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        book_id: bookId,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }),
                    keepalive: true
                }).then(response => {
                    console.log("Fetch thành công:", response.ok);
                }).catch(error => {
                    console.error("Fetch thất bại:", error);
                });
            }
        } catch (error) {
            console.error("❌ Lỗi khi kết thúc phiên đọc:", error);
        }

        // Thông báo cho người dùng
        showStatus("Đã lưu tiến độ đọc", "check-circle");
    }

    // Hàm hiển thị trạng thái dạng toast
    function showStatus(message, icon = 'info-circle') {
        const statusDiv = document.getElementById('reader-status');
        statusDiv.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
        statusDiv.classList.add('show');

        setTimeout(() => {
            statusDiv.classList.remove('show');
        }, 2000);
    }

    // Xử lý chế độ tối
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const toggleHandle = document.querySelector('.toggle-handle');
    const body = document.body;

    // Kiểm tra nếu user đã bật chế độ tối trước đó
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        darkModeToggle.checked = true;
        toggleHandle.style.transform = 'translateX(16px)';
        document.querySelector('.toggle').style.backgroundColor = '#007bff';
    }

    // Xử lý khi toggle chế độ tối
    darkModeToggle.addEventListener('change', function () {
        if (this.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
            toggleHandle.style.transform = 'translateX(16px)';
            document.querySelector('.toggle').style.backgroundColor = '#007bff';
            showStatus('Đã bật chế độ tối', 'moon');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
            toggleHandle.style.transform = 'translateX(0)';
            document.querySelector('.toggle').style.backgroundColor = '#dee2e6';
            showStatus('Đã tắt chế độ tối', 'sun');
        }
    });
});
