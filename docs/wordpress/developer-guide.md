# راهنمای توسعهٔ 0.4.0

Runtime: theme/elinstyle و plugins/elinstyle-companion؛ در مخزن عمومی زیر wordpress/themes و wordpress/plugins قرار دارند. دموهای المنتور در JSON همراه Companion و گوتنبرگ در patterns قالب‌اند. بلوک‌های پویا و ویجت فیلتر در Companion و shell/دارایی‌ها در قالب‌اند.

مولد المنتور build-elementor.py به BeautifulSoup4 و سه فایل مولد کنار هم نیاز دارد؛ ELINSTYLE_REFERENCE_DIR مسیر HTML مصوب است. خروجی دادهٔ قابل ویرایش بومی است و نباید با HTML تمام‌صفحه جایگزین شود. مرجع HTML خواندنی است. ابزار بسته‌بندی فقط دو پوشهٔ محصول را ZIP می‌کند؛ vendor و ابزار/fixture/config وارد بسته نمی‌شوند.

گوتنبرگ با elinstyle-surface محدود می‌شود. برای اصلاح المنتور ابتدا کنترل موجود در UI را پیدا کنید؛ CSS مخصوص ویجت در همان Custom CSS، CSS صفحه/تمپلیت در همان سند و CSS مشترک در kit Site Settings باشد. CSS اختصاصی دمو به فایل عمومی قالب اضافه نشود. elementor-widget-map.json دلیل هر استثنا را ثبت می‌کند.

کاتالوگ، طبقهٔ product_cat و ویژگی‌های pa_size/pa_color/pa_fabric را می‌خواند. پیوندهای نمونه women/men/basics باید با دادهٔ واقعی فروشگاه تطبیق داده شوند. فیلتر GET فقط خواندنی است؛ قیمت/موجودی/سفارش از APIهای Woo می‌آیند. در صفحات المنتور، form handling گوتنبرگ و overrideهای cart/checkout/account اختصاصی آن وارد مسیر native Pro نمی‌شوند.

تست importer-integration-qa.php فقط از WP-CLI با --user مدیر، ELINSTYLE_QA_DISPOSABLE=1، مرحلهٔ مشخص و hostname 127.0.0.1 روی نصب کاملاً دورریختنی اجرا شود؛ محتوای آزمایشی می‌نویسد/حذف می‌کند. مرحله‌های no-woo، gutenberg، missing-pro، elementor با فعال‌سازی وابستگی‌های مربوط در درخواست‌های جدا اجرا می‌شوند. harness موجود بودن عملیات قبلی را برای جلوگیری از بازنویسی رد می‌کند.

تست‌های مرورگر نیازمند config و cookie خصوصی بیرون مخزنند. اعتبارسنجی باید ذخیره/بازگشایی واقعی، اثر تنظیمات در فرانت، انتخاب مستقل kit، تکرار/ادامه/rollback و حفظ دادهٔ دموی اول را پوشش دهد. fixture، سفارش محلی، cookie، کلید، config و دیتابیس قابل انتشار نیستند.

حداقل نحوی کد PHP7.4 با نیازهای runtime وابستگی‌ها تفاوت دارد؛ نسخهٔ آزموده‌شده در compatibility.md ثبت شده است. پس از ارتقای Woo، دو override تاریخی گوتنبرگ cart/cart.php و checkout/form-checkout.php بازبینی شوند. Elementor فقط در مسیر خودش از template بومی Woo استفاده می‌کند. سایر Theme Builder conditions خودکار عوض نمی‌شوند.
