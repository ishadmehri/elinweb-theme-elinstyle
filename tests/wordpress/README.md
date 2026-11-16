# آزمون‌های WordPress 0.4.0

شواهد پاک‌شدهٔ نسخهٔ جاری در evidence/0.4.0 و حدود آزمون در docs/wordpress/elementor-test-matrix.md هستند. فایل‌های نشست، credentials، دیتابیس، fixture خصوصی و Pro منتشر نمی‌شوند.

## درون‌ریز

فقط در نصب **کاملاً دورریختنی** با hostname 127.0.0.1 و مدیر واقعی از WP-CLI اجرا کنید. ELINSTYLE_QA_DISPOSABLE=1 و ELINSTYLE_QA_PHASE را تعیین کنید و `wp --path=<disposable-site> --user=<admin-id> eval-file <absolute-path>/importer-integration-qa.php` را اجرا کنید. این تست محتوای آزمایشی می‌سازد/ویرایش/حذف می‌کند؛ روی فروشگاه جاری اجرا نشود.

ترتیب: theme+Companion فعال و Woo غیرفعال → no-woo؛ Woo فعال → gutenberg؛ Core فعال و Pro غیرفعال → missing-pro؛ Core+Pro مجاز فعال → elementor. فعال‌سازی هر وابستگی در درخواست جدا باشد. هر مرحله خروجی passed دارد و job کامل موجود را برای حفاظت از داده رد می‌کند. نگذارید تنظیمات/محتوای دیگری حین تست تغییر کند.

## ویرایشگر المنتور

پس از آماده‌کردن دو دمو و انتخاب مستقل kit در محیط تست، ELINSTYLE_QA_CONFIG را به JSON خصوصی بیرون Git بدهید: base (URL محلی)، ids با home و kit و cookiesFile (فایل خصوصی آرایهٔ cookie صادرشده از نشست مدیر همین نصب). ELINSTYLE_QA_DISPOSABLE=1 الزامی است؛ credential ثابت در ریپو نیست. `npm run elementor:editor` و سپس `npm run elementor:kit` اجرا شوند. BROWSER_EXECUTABLE اختیاری است؛ در غیر این صورت Chromium نصب‌شدهٔ Playwright استفاده می‌شود. این تست‌ها تنظیمات را موقتاً تغییر و با ویرایشگر بازمی‌گردانند؛ شکست میانی می‌تواند نیاز به بازگردانی دستی fixture داشته باشد. نتیجه در .artifacts است.

مقادیر Style از فرمان native خود ویرایشگر و ذخیره از دکمهٔ واقعی UI تغییر می‌کنند؛ این تست ادعا نمی‌کند همهٔ کنترل‌ها با کلیک ماوس پوشش داده شده‌اند. Content title، پنل Style، save/reopen و اثر CSS واقعی فرانت بررسی می‌شوند.

## تست‌های قبلی گوتنبرگ/Woo

# آزمون‌های WordPress

از این پوشه `npm install` و نصب Chromium Playwright را انجام دهید. سپس `npm run lint` و `npm run smoke` اجرا شوند. متغیرهای محیطی PHP_BINARY، WP_BASE_URL و BROWSER_EXECUTABLE اختیاری‌اند؛ پیش‌فرض URL محلی elistyle.local است. هیچ credential برای smoke لازم نیست و نتیجه فقط وضعیت/شمار است؛ محتوای مشتری چاپ نمی‌شود.

`npm run catalog` آزمون اصلی انتخاب چندگانه، GET/AJAX، بازهٔ قیمت متغیر، خطا/بازیابی، تاریخچه، فوکوس و بدونJS است. این آزمون به چهار محصول دمو و همان taxonomy/stock محیط QA اصلی نیاز دارد؛ روی مجموعهٔ محصول دلخواه، انتظار شمار fixture صحیح نیست. WP_BASE_URL را به محیط توسعهٔ ایزوله نسبت دهید. گزارش محلی در .artifacts نوشته می‌شود و در Git نیست.

`npm run account` منبع آزمون حساب واقعی است و فقط روی محیط دورریختنی با مشتری و سفارش‌های آزمایشی از پیش آماده اجرا شود؛ نشانی و مشخصات همان مشتری آزمایشی را ویرایش و بازیابی رمز او را درخواست می‌کند. ACCOUNT_FIXTURE_FILE به JSON خارج از Git با فیلدهای login، password و orders (دو شناسه متعلق به دو مشتری متفاوت) و ACCOUNT_INSTALL_FILE به JSON خارج Git با url حساب (با / پایانی) و login URL اشاره کنند. ساخت/پاک‌سازی این مشتریان باید توسط اپراتور محیط QA انجام شود؛ ابزار ساخت کاربران/رمز و dump محلی منتشر نشده است. credentialهای نمونهٔ ثابت یا واقعی در این ریپو قرار ندهید. این آزمون در تحویل محلی اجرا و fixtureهایش پاک شدند؛ برای smoke عمومی لازم نیست.

شواهد evidence خلاصهٔ پاک‌شدهٔ اجرای واقعی0.3.0 هستند؛ نبود raw تصاویر/نشست/توکن/نشانی عمدی است. نسخهٔ PHP7.4 با lint و runtime حساب روی PHP8.2.29 بررسی شده است. PHP7.4 runtime کامل و ارسال ایمیل بیرونی/ثبت‌نام فعال/درگاه/صفحه‌خوان تست نشده‌اند.

برای `npm run gutenberg:editor` همان config خصوصی باید فیلد gutenberg با شناسه‌های واقعی home/shop/guide/account در همان base داشته باشد. این آزمون چهار سند را ذخیره و بازگشایی و اعتبار بلوک‌ها را بررسی می‌کند؛ شناسه‌های نصب دیگر را استفاده نکنید.
