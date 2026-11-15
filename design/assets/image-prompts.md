# منشأ تصاویر طراحی v1

روش: ابزار داخلی `image_gen.imagegen` طبق مهارت imagegen؛ بدون CLI/API fallback. تاریخ تولید 2026-11-11. این تصاویر صرفاً دارایی نمایشی طراحی‌اند و عکس کالای واقعی نیستند.

## عکس ادیتوریال

خروجی پروژه: `editorial-hero-v1.png`، اندازهٔ واقعی ۱۵۳۶ × ۱۰۲۴.

اصل تولیدشده: `C:\Users\imans\.codex\generated_images\01a1cb52-81fc-7352-ab5c-ac501d3dc067\exec-71e45b55-febd-4d8f-b9ec-27516a5cfb53.png`

متن درخواست:

> Use case: photorealistic-natural. Asset type: fashion editorial hero photograph for a Persian modern everyday womenswear and menswear storefront. Create one premium photographic landscape image, 1536x1024 composition. Two adult Iranian-looking fashion models, woman wearing a modest oversized warm ivory long sleeve shirt over a cream top and charcoal wide leg trousers, man wearing an olive overshirt over an off-white T-shirt and dark trousers. Full body or nearly full body, natural relaxed poses, tasteful quiet contemporary fashion, realistic skin and cloth, no logos. Woman on left half, man on right half, clean warm limestone architectural courtyard with monolithic wall and a gentle geometric shadow, subtle sunlit olive branch shadow, soft editorial midday light. Both models visible with breathing space, subject central, restrained palette ivory stone olive charcoal. High-end magazine photography, natural fabric texture, genuine photographic realism. No writing, no collage, no interface, no watermark.

## چهار عکس محصول

خروجی پروژه: `catalog-contact-sheet-v1.png`، اندازهٔ واقعی ۱۲۵۴ × ۱۲۵۴؛ اندازهٔ خروجی ابزار با اندازهٔ پیشنهادی درخواست متفاوت است. برش‌ها از نصف ابعاد واقعی محاسبه می‌شوند.

اصل تولیدشده: `C:\Users\imans\.codex\generated_images\01a1cb52-81fc-7352-ab5c-ac501d3dc067\exec-6e0f3bf6-167d-425f-a013-15ac12b81826.png`

متن درخواست:

> Use case: product-mockup. Asset type: a 2x2 apparel catalog contact sheet, four separate photographic product flat lays. Square 1536x1536 image, exactly four equal square quadrants with no gutters, all backgrounds identical soft pale warm-gray #E8E6E0. Each quadrant shows precisely one complete garment centered with generous 15 percent clear margin. Upper left: ivory women's oversized long-sleeve button-up cotton shirt with collar, laid flat neatly, visible realistic textile folds. Upper right: olive green men's lightweight overshirt button-front two pockets, laid flat neatly. Lower left: charcoal women's tailored wide leg full-length trousers neatly flat, waist to hem entirely visible. Lower right: warm oatmeal ribbed crew-neck knit sweater, sleeves gently angled down, entire garment visible. Uniform overhead studio product photography, delicate shadow on neutral background, elegant premium clothing, realistic seams and knit texture. No models, no body parts, no mannequins, no accessories, no hanger, no writing, no logo, no frames. Garments must not cross quadrant boundaries.

## استفاده در طرح

عکس‌ها ابتدا مشاهده شدند، سپس بدون تغییر نقاشی یا بازسازی لباس در SVG قرار گرفتند. کادرهای viewBox برش نمایشی هر محصول یا دسته را تعیین می‌کنند. تمام متن رابط با فونت واقعی از دیسک رندر شده است. عکس بافت عمداً در دستهٔ لباس پایه و کمپین تکرار می‌شود تا تم مجموعه ثابت بماند؛ برای محصول واقعی بهتر است عکس ادیتوریال مستقل کمپین تهیه شود.
