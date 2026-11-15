> سند منتقل‌شده از تحویل محلی؛ مسیرهای Local و نام گزارش‌های قدیمی، سابقهٔ آزمون هستند. راهنمای جاری ریپو در README.md همین پوشه است؛ داده‌ها و تصاویر خصوصی QA منتشر نشده‌اند.

# راهنمای توسعه

## ساختار و مسئولیت

- `wordpress/themes/elinstyle`: shell، APIهای enqueue، theme.json، الگوها و دارایی‌های محلی.
- `wordpress/plugins/elinstyle-companion`: ثبت بلوک‌ها، query کاتالوگ، فرم نمایشی و ورود دمو با capability و nonce.
- `tools/build.py`: مولد اولیهٔ نسخه0.1 از مرجع استاتیک؛ برای ساخت اولیه است. در نسخهٔ جاری، CSS خرید با build-product.py و CSS فیلترها با build-catalog.py به‌روز می‌شود؛ کد runtime قالب/افزونه مستقیماً نگهداری می‌شود. ابزارها به BeautifulSoup4 و tinycss2 در tools/python نیاز دارند و مرجع را تغییر نمی‌دهند.
- `tools/blueprint*.json` و `local-demo.ps1`: راه‌اندازی محیط موقت؛ fixture Woo فقط برای تست.
- `tools/qa.cjs`، `woo-qa.cjs`، `php-check.cjs`: تست محلی؛ `refresh.cjs` فقط برای بازنشانی محتوای همین دموی دورریختنی در 9411 با شناسه‌های ثبت‌شده است و روی سایت دیگر اجرا نشود.
- `qa/`: PNG و JSON شواهد؛ `dist/`: ZIP نصب.

## توسعه و حفاظت از مرجع

تغییر بصری باید ابتدا در طرح/مرجع مربوط تأیید شود؛ shell یا بلوک نباید بهانهٔ تغییر بدون ثبت طراحی باشد. از data و ساختار بلوک برای متن و محتوا استفاده کنید. فایل‌های تولیدشدهٔ الگو را با تغییر build.py هماهنگ نگه دارید. CSS مرجع به `.elinstyle-surface` محدود است؛ هیچ CSS عمومی برای wp-admin اضافه نشود. CSS لازم فقط برای تفاوت DOM بلوک در blocks.css و برای canvas در editor.css ثبت شود. در حالت غیر iframe نیز scope مانع تغییر رابط مدیریت است.

CSS theme.json و shared.css هنگام تغییر توکن‌ها با هم به‌روز شوند؛ fonteهای ۴۰۰/۶۰۰/۷۰۰ محلی‌اند. URLهای محتوای واردشده توسط API وردپرس تولید می‌شوند؛ پس از جابه‌جایی دامنه از ابزار مهاجرت معتبر و حفظ serialization استفاده شود. محتوای الگوهای خصوصی فارسی خود دادهٔ قابل ویرایش است؛ رشته‌های کاربردی PHP/JS از توابع ترجمه استفاده می‌کنند.

برای توسعه کاتالوگ، طبقه‌های `product_cat` و ویژگی‌های `pa_size`، `pa_color` و `pa_fabric` مبنا هستند. لینک‌های زنانه/مردانه/پایه به slugهای `women`، `men` و `basics` اشاره می‌کنند؛ روی فروشگاه واقعی slug را مطابق داده تنظیم کنید. قیمت و موجودی از WC_Product و قیمت نمایشی از get_price_html می‌آیند. کنترل موجودی و سبد به Woo سپرده شده است. فرمول قیمت یا write مستقیم meta وجود ندارد. فیلتر ورودی عمومی GET فقط خواندنی است؛ nonce برای نوشتنِ ورود دمو لازم است و کنترل manage_options دارد.

از0.2.1، catalog.php رابط و query فیلترها را نگه می‌دارد؛ category/size/color/fabric آرایهٔ نامک یا مقدار اسکالر قدیمی را می‌پذیرند. catalog.js همین GET سرور را با fetch و تاریخچهٔ URL اجرا می‌کند و fallback فرم عادی حفظ است. فیلتر و مرتب‌سازی قیمت از lookup Woo با هم‌پوشانی بازه و تعدیل مالیات استفاده می‌کند. ADR-003-catalog-filters.md، archive-interaction-qa.cjs و archive-verify.py مرجع تصمیم و آزمون جاری‌اند.

## اجرای Woo محلی

WooCommerce 10.8.0 را از `https://downloads.wordpress.org/plugin/woocommerce.10.8.0.zip` در `tools/vendor` استخراج کنید و از ریشه اجرا کنید:

```powershell
node tools/node_modules/@wp-playground/cli/cli.js server --wp=6.9 --php=7.4 --port=9412 --workers=2 --mount-dir wordpress/themes/elinstyle /wordpress/wp-content/themes/elinstyle --mount-dir wordpress/plugins/elinstyle-companion /wordpress/wp-content/wordpress/plugins/elinstyle-companion --mount-dir tools/vendor/woocommerce /wordpress/wp-content/plugins/woocommerce --blueprint tools/blueprint-woo.json --login
```

ابتدا `/wp-admin/` را باز کنید تا نشست محلی مدیر برقرار شود؛ حالت Coming soon تازهٔ Woo ممکن است برای ناشناس کاتالوگ را بپوشاند. این تنظیم در سایت واقعی خودکار تغییر نمی‌کند. fixture چهار محصول و یک محصول متغیر با سایز ناموجود می‌سازد؛ تصویر contact-sheet صرفاً دادهٔ آزمون است و عکس نهایی تک‌محصول نیست. runtime این محیط SQLite است؛ MySQL/MariaDB و پرداخت واقعی جداگانه باید بررسی شوند.

## انتشار و نگهداری

در نسخه0.2.0 دو override ساختاری cart/cart.php و checkout/form-checkout.php با نسخهٔ مرجع و علت در ADR-002-purchase-flow.md ثبت شده‌اند؛ پس از ارتقای Woo باید بازبینی شوند. MySQL8.4.0 روی سایت Local واقعاً آزموده شده است. پیش از انتشار: مجوز دارایی‌ها و لایسنس، PHPCS/WPCS، بررسی دستی دسترس‌پذیری و بازبینی بصری نهایی تکمیل شوند. ورود دمو در صورت خطا ممکن است بخشی از صفحات را ساخته باشد؛ شناسه‌ها را در محیط محلی بررسی و از نسخهٔ پشتیبان/محیط خالی بازسازی کنید؛ حذف خودکار محتوا ندارد. نصب روی elistyle.local با درخواست مستقیم کاربر انجام شده؛ انتشار عمومی در دامنهٔ این مأموریت نیست.

## منابع رسمی بررسی‌شده

- [الزامات WordPress](https://wordpress.org/about/requirements/)
- [الزامات WooCommerce](https://woocommerce.com/document/server-requirements/)
- [سربرگ نسخهٔ دقیق Woo 10.8.0](https://plugins.svn.wordpress.org/woocommerce/tags/10.8.0/woocommerce.php)؛ فایل ZIP دریافت‌شده هم بررسی شده است.
- [theme.json v3؛ WordPress 6.6+](https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/theme-json-living/)
- [بارگذاری دارایی‌های بوم و iframe](https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/)
- [پشتیبانی قالب از ویرایشگر](https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/)
- [WordPress Playground CLI](https://developer.wordpress.org/playground/developers/local-development/wp-playground-cli/)

اسناد آنلاین زنده‌اند؛ ملاک آزمون، نسخهٔ واقعی ثبت‌شده در ماتریس است. حداقل PHP افزونه/قالب 7.4 با PHP پیشنهادی میزبانی یکی نیست. توصیه‌های سرور به معنی تضمین سازگاری همهٔ نسخه‌های Woo نیست.
