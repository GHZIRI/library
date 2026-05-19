/**
 * ════════════════════════════════════════════════════════════════════════════
 * SIMPLE TEST FILE - JavaScript
 * اختبر هل الـ Fetch يتصل بالـ API الصحيح؟
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * استخدام:
 * 1. أضف هذا الـ script في HTML:
 *    <script src="../assets/js/test_fetch.js"></script>
 * 
 * 2. افتح الصفحة وتحقق من Browser Console (F12)
 * 3. اتبع الـ logs بالترتيب لمعرفة أين المشكلة
 */

console.log('═══════════════════════════════════════════════════════════════════');
console.log('🔍 STARTING FETCH TEST - Check this console for debug messages');
console.log('═══════════════════════════════════════════════════════════════════');

// ════════════════════════════════════════════════════════════════════════════
// TEST 1: هل الـ Script يعمل؟
// ════════════════════════════════════════════════════════════════════════════
console.log('✅ TEST 1: Script loaded successfully');
console.log('   Location: assets/js/test_fetch.js');

// ════════════════════════════════════════════════════════════════════════════
// TEST 2: بناء الـ URL
// ════════════════════════════════════════════════════════════════════════════
const API_URL = '../api/test_books.php';
console.log('');
console.log('📍 TEST 2: API URL');
console.log('   URL:', API_URL);
console.log('   Full URL:', window.location.origin + window.location.pathname.replace('views/catalogue.php', '') + API_URL);

// ════════════════════════════════════════════════════════════════════════════
// TEST 3: محاولة الـ Fetch
// ════════════════════════════════════════════════════════════════════════════
console.log('');
console.log('🚀 TEST 3: Sending Fetch Request');
console.log('   Sending to:', API_URL);
console.log('   Time:', new Date().toLocaleTimeString());

fetch(API_URL)
    .then(response => {
        console.log('');
        console.log('✅ TEST 4: Response Received');
        console.log('   HTTP Status:', response.status);
        console.log('   Status Text:', response.statusText);
        console.log('   Content-Type:', response.headers.get('content-type'));
        
        // إذا كان HTTP status غير 200
        if (!response.ok) {
            console.error(`   ⚠️ WARNING: HTTP ${response.status} ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('');
        console.log('✅ TEST 5: JSON Parsed Successfully');
        console.log('   Data received:', data);
        console.log('   Data type:', typeof data);
        console.log('   Data keys:', Object.keys(data));
        
        // تحقق من البيانات
        if (data.success === true) {
            console.log('   ✅ success = true');
        } else {
            console.log('   ❌ success is not true:', data.success);
        }
        
        if (data.items) {
            console.log('   ✅ items array exists');
            console.log('   Items count:', data.items.length);
            console.log('   First item:', data.items[0]);
        } else {
            console.log('   ❌ items array NOT found');
        }
    })
    .catch(error => {
        console.log('');
        console.log('❌ TEST ERROR: Fetch Failed');
        console.log('   Error:', error.message);
        console.log('   Error type:', error.name);
        console.log('   Stack:', error.stack);
        
        // تشخيص شائع للأخطاء
        if (error.message.includes('404')) {
            console.error('   → الملف غير موجود! تحقق من المسار');
        } else if (error.message.includes('500')) {
            console.error('   → خطأ في الـ Server! تحقق من PHP');
        } else if (error.message.includes('network')) {
            console.error('   → مشكلة في الاتصال بالشبكة');
        } else if (error.message.includes('JSON')) {
            console.error('   → الـ Response ليست JSON صحيحة! تحقق من api/test_books.php');
        }
    })
    .finally(() => {
        console.log('');
        console.log('═══════════════════════════════════════════════════════════════════');
        console.log('🏁 TEST COMPLETED');
        console.log('═══════════════════════════════════════════════════════════════════');
    });

console.log('');
console.log('⏳ Waiting for response... (انتظر الرسالة التالية)');
