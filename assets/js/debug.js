/**
 * =============================================
 * ملف تشخيص المشكلة — debug.js
 * ضعه مؤقتاً في catalogue.php بدل main.js
 * =============================================
 */

console.log('═══════════════════════════════════');
console.log('🚀 debug.js بدأ يشتغل');
console.log('═══════════════════════════════════');

document.addEventListener('DOMContentLoaded', function () {

    // ─── خطوة 1: هل الـ booksContainer موجود في الصفحة؟
    const container = document.getElementById('booksContainer');
    if (container) {
        console.log('✅ خطوة 1: booksContainer موجود في الصفحة');
    } else {
        console.error('❌ خطوة 1: booksContainer غير موجود! — تحقق من HTML');
        return; // وقف هنا
    }

    // ─── خطوة 2: ما هو الـ URL الذي سيُرسل إليه الطلب؟
    // من views/catalogue.php المسار يكون:
    // ../api/ping.php  (يصعد مجلد ثم يدخل api)
    const testURL = '../api/ping.php';
    console.log('📡 خطوة 2: سيتم الاتصال بـ URL:', testURL);
    console.log('   (إذا الصفحة في: /library/views/catalogue.php)');
    console.log('   (الـ API يجب أن يكون في: /library/api/ping.php)');

    // ─── خطوة 3: إرسال طلب Fetch
    console.log('⏳ خطوة 3: إرسال طلب fetch...');

    fetch(testURL)
        .then(function (response) {
            // ─── خطوة 4: ماذا رجع من الـ Server؟
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('📥 خطوة 4: وصل رد من الـ Server');
            console.log('   ► HTTP Status Code:', response.status);
            console.log('   ► Status Text     :', response.statusText);
            console.log('   ► URL المستخدم    :', response.url);
            console.log('   ► هل الرد ناجح؟   :', response.ok ? 'نعم ✅' : 'لا ❌');

            if (!response.ok) {
                console.error('❌ خطوة 4: خطأ HTTP — الكود:', response.status);
                console.error('   السبب المحتمل:');
                if (response.status === 404) {
                    console.error('   → ملف ping.php غير موجود في /api/');
                } else if (response.status === 500) {
                    console.error('   → خطأ في PHP — افتح ping.php مباشرة في المتصفح');
                } else if (response.status === 401) {
                    console.error('   → الـ API يطلب تسجيل دخول');
                }
                throw new Error('HTTP Error: ' + response.status);
            }

            // ─── خطوة 5: تحويل الرد إلى JSON
            console.log('⏳ خطوة 5: تحويل الرد إلى JSON...');
            return response.text(); // نستخدم text() أولاً لنرى الرد الخام
        })
        .then(function (rawText) {
            // ─── خطوة 6: ماذا يحتوي الرد الخام؟
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('📄 خطوة 6: الرد الخام من PHP:');
            console.log(rawText);

            // هل يبدأ الرد بـ { أو [ ؟ (JSON صحيح)
            const trimmed = rawText.trim();
            if (!trimmed.startsWith('{') && !trimmed.startsWith('[')) {
                console.error('❌ خطوة 6: الرد ليس JSON!');
                console.error('   السبب المحتمل: PHP يطبع شيئاً قبل الـ JSON (error, warning, BOM)');
                console.error('   الرد يبدأ بـ:', trimmed.substring(0, 100));

                // عرض الخطأ في الصفحة بشكل واضح
                container.innerHTML = `
                    <div style="background:#1e1e1e;color:#ff6b6b;padding:20px;border-radius:8px;font-family:monospace;direction:ltr;text-align:left;grid-column:1/-1;">
                        <h3>❌ المشكلة: PHP يرجع HTML بدل JSON</h3>
                        <p>السبب: خطأ في PHP أو المسار غلط</p>
                        <pre style="background:#2d2d2d;padding:10px;border-radius:4px;overflow:auto;font-size:12px;">${escapeHtml(trimmed.substring(0, 500))}</pre>
                        <p>💡 الحل: افتح <a href="http://localhost/library/api/ping.php" target="_blank" style="color:#6c63ff">http://localhost/library/api/ping.php</a> في المتصفح مباشرة</p>
                    </div>
                `;
                return;
            }

            // ─── خطوة 7: تحويل النص إلى JSON
            let data;
            try {
                data = JSON.parse(rawText);
                console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                console.log('✅ خطوة 7: JSON صحيح! البيانات:');
                console.log(data);
                console.log('   ► success     :', data.success);
                console.log('   ► db_works    :', data.db_works);
                console.log('   ► db_message  :', data.db_message);
                console.log('   ► table_exists:', data.table_exists);
                console.log('   ► table_msg   :', data.table_msg);
                console.log('   ► books_in_db :', data.books_in_db);
                console.log('   ► items count :', data.items ? data.items.length : 0);
            } catch (e) {
                console.error('❌ خطوة 7: JSON.parse فشل!', e.message);
                return;
            }

            // ─── خطوة 8: هل الـ items موجودة؟
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            if (!data.items || data.items.length === 0) {
                console.warn('⚠️ خطوة 8: items فارغة أو غير موجودة');
                console.warn('   data.items:', data.items);
                container.innerHTML = '<div style="color:orange;padding:20px;grid-column:1/-1;">⚠️ items فارغة — PHP يعمل لكن لا توجد كتب</div>';
                return;
            }

            console.log('✅ خطوة 8: items موجودة! عددها:', data.items.length);
            console.log('   أول كتاب:', data.items[0]);

            // ─── خطوة 9: عرض الكتب الوهمية في الصفحة
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🎨 خطوة 9: عرض الكتب في الصفحة...');
            displayDummyBooks(data.items, container);

            // ─── ملخص نهائي
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('📊 ملخص التشخيص:');
            console.log('   PHP يعمل          :', '✅');
            console.log('   DB connection     :', data.db_works ? '✅' : '❌ ' + data.db_message);
            console.log('   جدول books        :', data.table_exists ? '✅ ' + data.books_in_db + ' كتاب' : '❌ ' + data.table_msg);
            console.log('   items في الـ JSON :', data.items.length > 0 ? '✅' : '❌');
            console.log('   عرض في الصفحة    :', '✅ إذا ظهرت بطاقات');
            console.log('═══════════════════════════════════');

            if (!data.db_works) {
                console.error('🔴 المشكلة الرئيسية: قاعدة البيانات لا تعمل');
                console.error('   الحل: تأكد أن XAMPP يعمل وأن قاعدة البيانات "library" موجودة');
            } else if (!data.table_exists) {
                console.error('🔴 المشكلة الرئيسية: جدول books غير موجود');
                console.error('   الحل: شغّل ملف core/script.sql في phpMyAdmin');
            } else if (data.books_in_db === 0) {
                console.warn('🟡 المشكلة: جدول books فارغ — لا توجد كتب في قاعدة البيانات');
                console.warn('   الحل: أضف كتباً في phpMyAdmin');
            } else {
                console.log('🟢 كل شيء يعمل! المشكلة في دالة displayBooks في main.js');
                console.log('   السبب: main.js يتوقع element.volumeInfo.title (Google Books)');
                console.log('   لكن قاعدة البيانات ترجع element.title مباشرة');
            }
        })
        .catch(function (error) {
            // ─── خطوة X: فشل كامل
            console.error('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.error('💥 خطوة X: فشل Fetch كلياً!');
            console.error('   نوع الخطأ:', error.name);
            console.error('   رسالة الخطأ:', error.message);

            if (error.message.includes('Failed to fetch')) {
                console.error('   السبب: الـ Server لا يعمل — تأكد أن XAMPP شغّال');
            } else if (error.message.includes('NetworkError')) {
                console.error('   السبب: مشكلة شبكة أو CORS');
            }

            container.innerHTML = `
                <div style="background:#1e1e1e;color:#ff6b6b;padding:20px;border-radius:8px;grid-column:1/-1;">
                    <h3>💥 Fetch فشل كلياً</h3>
                    <p>${error.message}</p>
                </div>
            `;
        });
});

// ─── دالة عرض الكتب الوهمية
function displayDummyBooks(books, container) {
    container.innerHTML = '';

    books.forEach(function (book) {
        const card = document.createElement('div');
        card.className = 'book-card';
        card.style.cssText = 'background:#1e1e2e;border:2px solid #6c63ff;border-radius:12px;padding:16px;text-align:center;';

        card.innerHTML = `
            <div style="font-size:48px;margin-bottom:8px;">📚</div>
            <h3 style="color:#fff;font-size:14px;margin:0 0 4px;">${escapeHtml(book.title)}</h3>
            <p style="color:#aaa;font-size:12px;margin:0 0 4px;">${escapeHtml(book.author)}</p>
            <p style="color:#6c63ff;font-weight:bold;margin:0;">${book.price} MAD</p>
            <p style="color:#2ecc71;font-size:11px;margin-top:4px;">✅ من قاعدة البيانات</p>
        `;

        container.appendChild(card);
    });

    console.log('✅ تم عرض', books.length, 'كتاب في الصفحة بنجاح!');
}

// ─── دالة مساعدة للأمان
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
