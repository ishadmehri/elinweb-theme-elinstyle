# بازتولید محلی طراحی

منابع `design/source/` صرفاً ابزار ساخت بوم‌اند. هیچ کدام سایت یا اتصال خرید/ورود ایجاد نمی‌کنند. منابع اصلی در فضای کاری طراح حفظ شده‌اند؛ این کپی فقط مسیر ورودی/خروجی را برای ریپو تطبیق داده است.

پیش‌نیاز: Python با Pillow و fontTools برای ساخت، Node.js و Playwright/Chromium برای رندر. وابستگی‌ها در بسته vendored نشده‌اند. فونت‌های دارای مجوز را خارج از Git نگه دارید و `ELINSTYLE_FONT_ROOT` را به پوشهٔ حاوی YekanBakh-Regular.ttf، YekanBakh-SemiBold.ttf و YekanBakh-Bold.ttf تنظیم کنید. مسیر پیش‌فرض دستگاه طراح در metadata ثبت است. `PLAYWRIGHT_MODULE` می‌تواند مسیر ماژول محلی Playwright و `CHROME_PATH` مسیر Chrome را مشخص کند؛ در صورت نصب عادی Playwright، متغیر ماژول لازم نیست.

برای رندر منابع SVG موجود با فونت محلی، از ریشهٔ ریپو:

```powershell
$env:ELINSTYLE_FONT_ROOT = 'D:\Fonts\fontiran\YekanBakh3-ProPlus\Pro+\Yekan Bakh Family\ttf'
node design/source/render-previews.cjs cart-desktop-v1.svg checkout-desktop-v1.svg
```

بدون آرگومان، تمام بوم‌های source/artboards رندر می‌شوند. PNG و گزارش جدید فقط در design/generated/previews و design/generated/qa نوشته می‌شوند. این خروجی‌های محلی در Git نادیده گرفته شده‌اند؛ PNGهای canonical در design/previews بازنویسی نمی‌شوند.

برای بازسازی هندسهٔ بوم از Python، اسکریپت صفحهٔ موردنظر را اجرا کنید:

```powershell
python design/source/build-cart-checkout-design.py
python design/source/build-account-design.py
```

اسکریپت‌های build-design، build-shop-design، build-article-design، build-article-category-design، build-about-contact-design و build-product-design بقیهٔ بوم‌ها را می‌سازند. خروجی SVG در design/generated است و ممکن است فونت محلی را در خود داشته باشد؛ آن را commit نکنید. build-brand.py لوگوهای بازتولیدشده را در design/generated/brand می‌نویسد. check-contrast.py گزارش رنگ را از توکن‌های جاری به generated/qa می‌نویسد. این فایل‌های تولیدی از انتشار نقش طراحی کنار گذاشته شده‌اند.

منابع SVG آماده به تصاویر مشترک نسبی در design/assets و design/brand وصل‌اند. بازکردن مستقیم SVG بدون فونت نصب‌شده ممکن است متن جایگزین نشان دهد؛ PNG مرجع ظاهری قطعی است. رندرر فونت را فقط در حافظهٔ فرایند بارگذاری می‌کند و فایل فونت را به ریپو کپی نمی‌کند.
