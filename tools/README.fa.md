# اسکریپت مشترک انتشار قالب‌ها

هدف: هر نقش فقط با یک فرمان، تغییرات محدودهٔ خودش را commit و سپس روی remote تنظیم‌شده push کند. اسکریپت‌ها PowerShell هستند و به Git نیاز دارند؛ GitHub CLI فقط برای احراز هویت/ساخت ریپوی remote در مرحلهٔ راه‌اندازی مفید است.

## راه‌اندازی یک‌باره توسط مدیر

در یک مسیر جدید و مستقل، ساختار استاندارد را بسازید:

```powershell
.\Initialize-TemplateRepository.ps1 -Repository 'D:\programming\ChatGPTdev\elinweb-theme-elinstyle'
```

این فرمان فقط پوشه‌ها، manifest نقش‌ها، gitignore و ریپوی محلی را می‌سازد؛ فایل‌های پروژهٔ فعلی را منتقل نمی‌کند و ریپوی GitHub نمی‌سازد. Git پوشهٔ خالی را نگهداری نمی‌کند. برای ریپوی موجود، manifest نمونه را پس از بررسی در ریشه با نام `.template-publish.json` قرار دهید؛ initializer روی ریپوی موجود اجرا نمی‌شود.

مدیر یک‌بار فایل‌های فعلی را در ساختار استاندارد قرار می‌دهد، اطلاعات هویت Git و احراز هویت GitHub را تنظیم می‌کند و remote ریپوی انتخاب‌شده را اضافه می‌کند. عمومی/خصوصی بودن و URL مقصد باید تعیین شده باشد؛ اسکریپت حدس نمی‌زند و هیچ توکنی نمی‌خواهد. قبل از اولین انتشار تمام فایل‌های موجود، از جمله فونت‌ها و تصاویر، از نظر اجازهٔ انتشار و اطلاعات محلی بررسی شوند. `.gitignore` پایه جای این تصمیم را نمی‌گیرد.

## فرمان هر سشن

از ریشهٔ ریپوی استاندارد:

```powershell
# دیدن تغییرات مربوط به نقش، بدون staging/commit/push
.\tools\Publish-Template.ps1 -Repository . -Role design

# ثبت و ارسال تغییرات طراحی
.\tools\Publish-Template.ps1 -Repository . -Role design -Action Push -Message 'Approve homepage v2'

# ثبت و ارسال تغییرات فرانت
.\tools\Publish-Template.ps1 -Repository . -Role frontend -Action Push -Message 'Implement account pages'

# ثبت و ارسال تغییرات وردپرس
.\tools\Publish-Template.ps1 -Repository . -Role wordpress -Action Push -Message 'Connect native account flow'

# فقط commit محلی، بدون ارسال
.\tools\Publish-Template.ps1 -Repository . -Role frontend -Action Commit -Message 'Refine cart layout'
```

`-Message` برای Commit/Push الزامی است. remote پیش‌فرض origin است؛ `-Remote` قابل تنظیم است. شاخهٔ فعلی push می‌شود؛ اسکریپت شاخه را عوض نمی‌کند. بدون تغییر جدید، Push می‌تواند commitهای محلی قبلی را ارسال کند.

## مالکیت

| نقش | مسیرها |
|---|---|
| design | design، docs/design |
| frontend | html، docs/frontend، tests/frontend |
| wordpress | wordpress، demos، docs/wordpress، tests/wordpress |
| manager | فایل‌های ریشهٔ مشخص‌شده، tools، docs/project |

manifest برای هر قالب قابل تنظیم است. برای حفظ تفکیک، مسیرهای نقش‌ها نباید هم‌پوشانی داشته باشند. README ریشه و changelog مشترک با مدیر است؛ هر نقش مستندات خودش را در مسیر تعیین‌شده بنویسد.

## رفتار در تداخل و خطا

- قفل فایل مشترک Git، عملیات این اسکریپت را در یک ریپو یا worktreeهای مشترک سری می‌کند؛ سشن دوم در صورت اشغال قفل با پیام روشن متوقف و بعداً دوباره اجرا می‌شود.
- وجود هر فایل stageشده از قبل باعث توقف می‌شود؛ اسکریپت چیزی را خودسرانه unstage/stash/reset نمی‌کند.
- فقط مسیرهای نقش stage و commit می‌شوند، اما **push کل تاریخچهٔ ارسال‌نشدهٔ شاخهٔ فعلی را می‌فرستد**؛ محدودکردن push به چند پوشه ویژگی Git نیست.
- اسکریپت فایل‌های نقش‌های دیگر را کپی یا همگام نمی‌کند؛ هر سشن باید در ساختار ریپوی درست کار کند. پوشه‌های فعلی D که هنوز استاندارد نشده‌اند با اجرای این ابزار خودکار مهاجرت نمی‌کنند.
- قفل فقط سشن‌هایی را هماهنگ می‌کند که همین اسکریپت را اجرا می‌کنند؛ هنگام اجرا عملیات دستی Git موازی انجام ندهید. اسکریپت ابزار تفکیک کار است، نه کنترل دسترسی امنیتی در GitHub.
- push بدون force است. اگر remote جلوتر باشد یا احراز هویت شکست بخورد، commit محلی حفظ و خطا گزارش می‌شود؛ حل اختلاف با مدیر است. هیچ pull/merge خودکاری انجام نمی‌شود.
- عمل Git روی شاخهٔ جداشده، merge/rebase نیمه‌تمام یا مسیر نقش عبوری از junction/symlink متوقف می‌شود.
- برای کارهای مستقل‌تر می‌توان برای هر نقش branch/worktree جدا و ادغام با PR داشت؛ این ابزار همان شاخهٔ انتخاب‌شده را منتشر می‌کند و خودکار PR را ادغام نمی‌کند.

## وضعیت تحویل

این ابزار با `Test-TemplatePublisher.ps1` روی ریپوی آزمایشی و remote محلی بررسی شده است: پیش‌نمایش بدون تغییر، commit محدود به نقش، حفظ stage موجود، قفل هم‌زمانی، push واقعی به remote محلی، حفظ تغییرات نقش دیگر، جلوگیری از commit خالی و حفظ commit محلی/تاریخچهٔ remote هنگام رد push. احراز هویت یا push واقعی GitHub هنوز بررسی نشده است. استفادهٔ روزمره پس از تنظیم یک‌بارهٔ ریپو، مسیرها و remote امکان‌پذیر است.
