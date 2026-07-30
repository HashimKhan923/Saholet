<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', config('app.name'))</title>
    <style>
        /* The letterhead (logo, corner decoration, watermark, footer wave/ribbon/contact bar)
           is now one pixel-perfect background image (public/images/Letterhead.png, matches
           the client's real letterhead exactly) instead of hand-drawn CSS shapes — DomPDF's
           <svg> support doesn't work at all in this build, so this is both simpler and more
           reliable than approximating it. It's `position: fixed`, which DomPDF repeats on
           every page (confirmed empirically) as long as it's a direct child of <body>.
           Dynamic content flows in the safe zone between the logo and the footer artwork. */
        @page { margin: 230px 0 240px 0; size: A4; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 0; }

        .toolbar { max-width: 794px; margin: 0 auto 16px auto; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-family: 'Helvetica', 'Arial', sans-serif; }
        .toolbar a { display: inline-flex; align-items: center; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; }
        .toolbar .primary { background: #1a7a35; color: #ffffff; border-color: #1a7a35; }

        @if ($__env->hasSection('toolbar'))
            /* Browser preview: real browsers have no concept of "PDF pages", so a single
               `<img>` background can only ever cover one page's worth of height — a document
               long enough to span 2+ real PDF pages would just run its extra rows straight
               over/past that one static image, including through the footer artwork baked
               into its bottom (that's the "text overlaps" bug). Using it as a *repeating* CSS
               background instead means the letterhead pattern keeps tiling underneath however
               tall the content makes the box — not a perfect page-by-page match to the real
               PDF, but no more content colliding with a footer graphic that isn't there. */
            body { background: #f1f5f9; margin: 0; padding: 24px 0; }
            .doc-heading { max-width: 794px; margin: 0 auto 12px auto; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 15px; font-weight: bold; color: #1e293b; }
            .preview-page {
                position: relative; width: 794px; min-height: 1123px; margin: 0 auto;
                background-color: #ffffff;
                background-image: url('{{ asset('images/Letterhead.png') }}');
                background-repeat: repeat-y;
                background-size: 794px 1123px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .content { padding-top: 230px; padding-bottom: 240px; }
        @else
            /* Real PDF output: an element with `position: fixed` that is a DIRECT child of
               <body> repeats on every page (confirmed empirically). Content flows and
               paginates normally between the reserved top/bottom margins. */
            .page-bg { position: fixed; top: -230px; left: 0; width: 794px; }
        @endif

        /* ---------- Content (flows across pages normally) ----------
           `position: relative` here is load-bearing, not decorative: a positioned element
           (`.page-bg` is `fixed`/`absolute`) always paints ABOVE plain static-flow content in
           CSS, regardless of DOM order — that's what was hiding the text behind the image.
           Making `.content` positioned too puts it in the same stacking order as `.page-bg`,
           where DOM order decides, and `.content` comes after — so it wins. */
        .content { position: relative; z-index: 1; width: 706px; margin: 0 auto; }

        .doc-custom-title { font-size: 13px; font-weight: bold; text-align: right; color: #334155; margin-top: 2px; }

        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; white-space: pre-line; }
        .box-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px; }
        .section-heading { font-size: 13px; font-weight: bold; color: #1a7a35; text-transform: uppercase; letter-spacing: 0.03em; padding-bottom: 4px; border-bottom: 2px solid #1a7a35; margin-bottom: 8px; }
        .section-text { font-size: 11px; color: #334155; white-space: pre-line; margin-bottom: 14px; }
        .provided-tagline { display: inline-block; margin-top: 3px; font-size: 9px; font-weight: bold; letter-spacing: 0.08em; text-transform: uppercase; color: #1a7a35; }

        .doc-title { font-size: 17px; font-weight: bold; text-align: right; color: #1a7a35; }
        .doc-meta { text-align: right; font-size: 10.5px; color: #64748b; margin-top: 4px; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; }
        table.items th { background: #1a7a35; color: #ffffff; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }

        table.totals { width: 100%; margin-top: 8px; }
        table.totals td { padding: 4px 10px; font-size: 12px; }
        table.totals .label { text-align: right; color: #64748b; }
        table.totals .value { text-align: right; width: 130px; font-weight: bold; }
        table.totals .grand td { border-top: 2px solid #1a7a35; font-size: 15px; font-weight: bold; color: #1a7a35; padding-top: 8px; }
        table.totals .discount-row td { color: #c4341f; font-weight: bold; }

        .status-box { margin-top: 20px; padding: 10px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 11px; color: #166534; }
        .notes-box { margin-top: 16px; font-size: 11px; color: #64748b; white-space: pre-line; }
    </style>
</head>
<body>
    @hasSection('toolbar')
        @if (trim($__env->yieldContent('doc-heading')) !== '')
            <div class="doc-heading">@yield('doc-heading')</div>
        @endif
        <div class="toolbar">
            @yield('toolbar')
        </div>
        <div class="preview-page">
    @endif

    @unless ($__env->hasSection('toolbar'))
        <img class="page-bg" src="{{ public_path('images/Letterhead.png') }}">
    @endunless

    <div class="content">
        <table style="width: 100%;">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    <div class="doc-title">@yield('doc-title')</div>
                    @hasSection('doc-custom-title')
                        <div class="doc-custom-title">@yield('doc-custom-title')</div>
                    @endif
                    <div class="doc-meta">@yield('doc-meta')</div>
                </td>
            </tr>
        </table>

        @yield('content')
    </div>

    @hasSection('toolbar')
        </div>
    @endif
</body>
</html>
