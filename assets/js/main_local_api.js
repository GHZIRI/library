/**
 * ════════════════════════════════════════════════════════════════════════════
 * مشروع المكتبة الرقمية - JavaScript (Local API Version)
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * شرح:
 * بدلاً من استخدام Google Books API، نستخدم API محلي يجلب من قاعدة البيانات
 * هذا أسرع وأكثر أماناً وبدون الحاجة لـ API Key
 */

// ── Local API Configuration ─────────────────────────────────────────────────
const API_BASE_URL = '../api/books.php';  // ✅ API محلي (من قاعدة البيانات)

// ── Initialize App on DOM Ready ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    console.log('📚 App initialized. Fetching initial books...');
    fetchBooks('');  // اجلب جميع الكتب
    setupEventListeners();
});

// ── Setup Event Listeners ───────────────────────────────────────────────────
const setupEventListeners = () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        // عند الضغط على Enter في حقل البحث
        searchInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchBooks();
            }
        });
    }
};

// ── Search Books by Query ───────────────────────────────────────────────────
const searchBooks = () => {
    const query = document.getElementById('searchInput')?.value?.trim();
    console.log(`🔍 Searching for: "${query}"`);
    fetchBooks(query);
};

// ── Fetch Books from Local API ──────────────────────────────────────────────
/**
 * شرح الدالة:
 * 1. أرسل طلب GET للـ API المحلي (/api/books.php)
 * 2. ننتظ الرد (JSON)
 * 3. نعرض الكتب في الصفحة
 * 4. إذا فشل: نعرض رسالة خطأ
 */
const fetchBooks = async (query = '') => {
    try {
        const container = document.getElementById('booksContainer');
        if (!container) {
            console.error('❌ booksContainer element not found!');
            return;
        }

        // ✅ عرض حالة التحميل (Loading State)
        container.innerHTML = '<div class="loading">⏳ Loading books...</div>';
        console.log('⏳ Loading...');

        // ✅ بناء URL الطلب
        let url = `${API_BASE_URL}?action=search&limit=20`;
        
        if (query && query.trim() !== '') {
            url += `&q=${encodeURIComponent(query)}`;
        }

        console.log(`📡 Fetching from: ${url}`);

        // ✅ إرسال الطلب
        const response = await fetch(url);

        // ✅ التحقق من HTTP Status
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        // ✅ تحويل الرد من JSON
        const data = await response.json();

        console.log('✅ Response received:', data);

        // ✅ التحقق من أن العملية نجحت
        if (!data.success) {
            throw new Error(data.message || 'API returned error');
        }

        // ✅ عرض الكتب
        displayBooks(data.items || []);

    } catch (error) {
        console.error('❌ Error fetching books:', error);
        const container = document.getElementById('booksContainer');
        if (container) {
            container.innerHTML = `
                <div class="error-state">
                    <p>❌ Failed to load books</p>
                    <p style="font-size: 12px; color: #666;">Error: ${error.message}</p>
                    <button onclick="location.reload()" style="margin-top: 10px; padding: 8px 15px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Try Again
                    </button>
                </div>
            `;
        }
    }
};

// ── Display Books as Cards ──────────────────────────────────────────────────
/**
 * شرح الدالة:
 * 1. امسح الـ Container
 * 2. إذا لم تكن هناك كتب: اعرض رسالة "No books found"
 * 3. لكل كتاب: أنشئ Card يحتوي على:
 *    - صورة الغلاف
 *    - العنوان
 *    - المؤلف
 *    - السعر
 *    - زر "View Details"
 */
const displayBooks = (books) => {
    const container = document.getElementById('booksContainer');
    if (!container) return;

    container.innerHTML = '';

    if (!books || books.length === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.textContent = '📚 No books found. Try a different search.';
        container.appendChild(emptyState);
        console.log('ℹ️ No books found');
        return;
    }

    console.log(`📚 Displaying ${books.length} books`);

    books.forEach((book) => {
        // ✅ بيانات الكتاب
        const title = book.title || 'No title';
        const author = book.author || 'Unknown author';
        const cover = book.cover_image || '../assets/images/no-cover.jpg';
        const priceBuy = book.price_buy ? `$${book.price_buy}` : 'N/A';
        const priceRental = book.price_rental ? `$${book.price_rental}` : 'N/A';
        const bookId = book.book_id;

        // ✅ إنشاء Card
        const card = document.createElement('div');
        card.className = 'book-card';

        // صورة الغلاف
        const img = document.createElement('img');
        img.src = cover;
        img.alt = title;
        img.loading = 'lazy';
        img.onerror = () => { img.style.display = 'none'; };
        img.style.cursor = 'pointer';
        img.onclick = () => window.location.href = `book_detail.php?id=${encodeURIComponent(bookId)}`;
        card.appendChild(img);

        // العنوان
        const titleEl = document.createElement('h3');
        titleEl.title = title;
        titleEl.textContent = title;  // ✅ آمن: textContent بدلاً من innerHTML
        titleEl.style.cursor = 'pointer';
        titleEl.onclick = () => window.location.href = `book_detail.php?id=${encodeURIComponent(bookId)}`;
        card.appendChild(titleEl);

        // المؤلف
        const authorEl = document.createElement('p');
        authorEl.textContent = `✍️ ${author}`;  // ✅ آمن
        authorEl.style.color = '#666';
        authorEl.style.fontSize = '14px';
        card.appendChild(authorEl);

        // الأسعار
        const priceEl = document.createElement('p');
        priceEl.innerHTML = `<strong>Buy:</strong> ${priceBuy} | <strong>Rent:</strong> ${priceRental}`;
        priceEl.style.fontSize = '13px';
        priceEl.style.color = '#27ae60';
        priceEl.style.margin = '8px 0';
        card.appendChild(priceEl);

        // زر التفاصيل
        const link = document.createElement('a');
        link.href = `book_detail.php?id=${encodeURIComponent(bookId)}`;
        link.textContent = '📖 View Details';
        link.className = 'btn-view-details';
        link.style.display = 'block';
        link.style.marginTop = '10px';
        link.style.padding = '8px';
        link.style.backgroundColor = '#3498db';
        link.style.color = 'white';
        link.style.textAlign = 'center';
        link.style.borderRadius = '4px';
        link.style.textDecoration = 'none';
        link.style.transition = 'background 0.3s';
        link.onmouseover = () => link.style.backgroundColor = '#2980b9';
        link.onmouseout = () => link.style.backgroundColor = '#3498db';
        card.appendChild(link);

        container.appendChild(card);
    });
};

// ── Utility: Show Alerts ────────────────────────────────────────────────────
const showAlert = (message, type = 'info') => {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) return;

    const alertClass = {
        success: 'alert-success',
        error: 'alert-error',
        info: 'alert-info'
    }[type] || 'alert-info';

    alertBox.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    setTimeout(() => {
        alertBox.innerHTML = '';
    }, 3000);
};

console.log('✅ main.js (Local API Version) loaded successfully');
