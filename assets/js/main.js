/**
 * ════════════════════════════════════════════════════════════════════════════
 * مشروع المكتبة الرقمية — JavaScript
 * المصدر: Open Library API (مجاني 100% — بدون مفتاح — بدون حدود)
 * الموقع: https://openlibrary.org/developers/api
 * ════════════════════════════════════════════════════════════════════════════
 */

// ── إعداد Open Library API ────────────────────────────────────────────────────
// هذا الـ URL مجاني تماماً ولا يحتاج أي مفتاح (API Key)
const OPEN_LIBRARY_SEARCH = 'https://openlibrary.org/search.json';
const OPEN_LIBRARY_COVERS = 'https://covers.openlibrary.org/b/id';

// الكلمة الافتراضية عند فتح الصفحة أول مرة
const DEFAULT_QUERY = 'arabic novels';

// ── تشغيل الكود بعد تحميل الصفحة ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ الصفحة جاهزة — Open Library API');
    fetchBooks(DEFAULT_QUERY);
    setupSearch();
});

// ── إعداد مربع البحث ──────────────────────────────────────────────────────────
const setupSearch = () => {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBooks();
        }
    });
};

// ── دالة البحث (تُستدعى من زر Search في HTML) ─────────────────────────────────
const searchBooks = () => {
    const query = document.getElementById('searchInput')?.value?.trim();
    const finalQuery = query || DEFAULT_QUERY;
    console.log(`🔍 البحث عن: "${finalQuery}"`);
    fetchBooks(finalQuery);
};

// ── الدالة الرئيسية: جلب الكتب من Open Library ───────────────────────────────
const fetchBooks = async (query) => {

    const container = document.getElementById('booksContainer');
    if (!container) {
        console.error('❌ booksContainer غير موجود في HTML');
        return;
    }

    // اعرض رسالة التحميل
    container.innerHTML = '<div class="loading">⏳ جار البحث عن الكتب...</div>';

    // بناء رابط الطلب
    // مثال: https://openlibrary.org/search.json?q=arabic&limit=20&fields=...
    const url = `${OPEN_LIBRARY_SEARCH}?q=${encodeURIComponent(query)}&limit=20&fields=key,title,author_name,cover_i,first_publish_year`;
    console.log('📡 رابط الطلب:', url);

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`خطأ HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('✅ رد Open Library:', data);
        console.log('   إجمالي النتائج:', data.numFound);

        // Open Library يرجع الكتب في مصفوفة اسمها "docs"
        const books = data.docs || [];
        displayBooks(books);

    } catch (error) {
        console.error('❌ فشل الطلب:', error.message);
        container.innerHTML = `
            <div class="error-state">
                <h3>❌ تعذّر تحميل الكتب</h3>
                <p>${error.message}</p>
                <p style="font-size:13px;color:#aaa;">تأكد من اتصالك بالإنترنت</p>
                <button onclick="fetchBooks('${query.replace(/'/g, "\\'")}')">🔄 إعادة المحاولة</button>
            </div>
        `;
    }
};

// ── دالة عرض الكتب ───────────────────────────────────────────────────────────
/**
 * بنية بيانات Open Library:
 *   book.title            ← عنوان الكتاب
 *   book.author_name[0]   ← اسم المؤلف الأول
 *   book.cover_i          ← رقم الغلاف (نبني منه الرابط)
 *   book.key              ← المعرّف مثل "/works/OL12345W"
 *   book.first_publish_year ← سنة النشر
 */
const displayBooks = (books) => {
    const container = document.getElementById('booksContainer');
    if (!container) return;

    if (!books || books.length === 0) {
        container.innerHTML = '<div class="empty-state">📚 لا توجد نتائج — جرّب كلمة بحث مختلفة.</div>';
        return;
    }

    container.innerHTML = '';

    books.forEach(book => {

        // ─── استخرج البيانات
        const title  = book.title || 'بدون عنوان';
        const author = book.author_name ? book.author_name[0] : 'مؤلف غير معروف';
        const year   = book.first_publish_year || '';

        // رابط صورة الغلاف — إذا cover_i موجود نبني الرابط، وإلا نضع placeholder
        const coverUrl = book.cover_i
            ? `${OPEN_LIBRARY_COVERS}/${book.cover_i}-M.jpg`
            : null;

        // المعرّف الفريد (نزيل / من البداية)
        const bookId = book.key ? book.key.replace('/works/', '') : '';

        // ─── أنشئ البطاقة
        const card = document.createElement('div');
        card.className = 'book-card';

        // الغلاف أو placeholder
        if (coverUrl) {
            const img = document.createElement('img');
            img.src = coverUrl;
            img.alt = title;
            img.loading = 'lazy';
            img.onerror = () => {
                // إذا فشلت الصورة: اعرض emoji بدلها
                img.replaceWith(makePlaceholder());
            };
            card.appendChild(img);
        } else {
            card.appendChild(makePlaceholder());
        }

        // العنوان
        const titleEl = document.createElement('h3');
        titleEl.textContent = title;
        titleEl.title = title;
        card.appendChild(titleEl);

        // المؤلف
        const authorEl = document.createElement('p');
        authorEl.textContent = `✍️ ${author}`;
        card.appendChild(authorEl);

        // سنة النشر (إذا موجودة)
        if (year) {
            const yearEl = document.createElement('p');
            yearEl.style.cssText = 'font-size:12px;color:#888;';
            yearEl.textContent = `📅 ${year}`;
            card.appendChild(yearEl);
        }

        // زر التفاصيل
        const link = document.createElement('a');
        link.href = `book_detail.php?id=${encodeURIComponent(bookId)}`;
        link.textContent = 'عرض التفاصيل';
        card.appendChild(link);

        container.appendChild(card);
    });

    console.log(`✅ تم عرض ${books.length} كتاب`);
};

// ── دالة مساعدة: placeholder للكتب بدون غلاف ────────────────────────────────
const makePlaceholder = () => {
    const div = document.createElement('div');
    div.style.cssText = `
        width: 100%;
        aspect-ratio: 2/3;
        background: linear-gradient(135deg, #2a2a3e, #1a1a2e);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        border-radius: 6px;
    `;
    div.textContent = '📚';
    return div;
};

// ── دالة مساعدة: عرض التنبيهات ───────────────────────────────────────────────
const showAlert = (message, type = 'info') => {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) return;

    const colors = { success: '#2ecc71', error: '#e74c3c', info: '#6c63ff' };
    alertBox.innerHTML = `
        <div style="background:${colors[type] || colors.info};color:#fff;padding:12px 20px;border-radius:8px;">
            ${message}
        </div>
    `;
    setTimeout(() => { alertBox.innerHTML = ''; }, 3000);
};
