{{-- Shared print styling for both documents. wkhtmltopdf renders with its own
     Qt WebKit build, so this stays to plain CSS — no flexbox gaps, no custom
     properties, no web fonts. --}}
<style>
    * { box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
        font-size: 11pt;
        line-height: 1.45;
        color: #1c1b1f;
        margin: 0;
    }
    h1, h2, h3 { margin: 0 0 6px; }
    .muted { color: #767586; }
    .small { font-size: 9pt; }

    .cover { padding-bottom: 10px; }
    .cover h1 { font-size: 22pt; letter-spacing: -0.3px; }
    .cover .sub { font-size: 11pt; color: #767586; margin-bottom: 18px; }

    .rule { border: 0; border-top: 2px solid #4648d4; margin: 10px 0 16px; }
    .thin-rule { border: 0; border-top: 1px solid #e0e3e5; margin: 14px 0; }

    table.meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.meta td { padding: 6px 8px; border: 1px solid #e0e3e5; vertical-align: top; }
    table.meta td.k { width: 26%; background: #f4f4f8; font-weight: bold; color: #45464f; }

    .tiles { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 6px; }
    .tiles td {
        width: 25%;
        border: 1px solid #e0e3e5;
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        background: #fafaff;
    }
    .tiles .n { font-size: 18pt; font-weight: bold; color: #4648d4; display: block; }
    .tiles .l { font-size: 8pt; color: #767586; text-transform: uppercase; letter-spacing: 0.5px; }

    .chip {
        display: inline-block;
        border: 1px solid #d5d6e0;
        border-radius: 10px;
        padding: 1px 7px;
        margin: 0 3px 4px 0;
        font-size: 8.5pt;
        background: #f7f7fb;
    }

    .page-break { page-break-before: always; }

    .q { page-break-inside: avoid; margin-bottom: 14px; }
    .q .head { margin-bottom: 4px; }
    .q .num {
        display: inline-block;
        min-width: 22px;
        font-weight: bold;
        color: #4648d4;
    }
    .q .text { font-weight: 600; }
    .q .marks { float: right; font-size: 9pt; color: #45464f; white-space: nowrap; }
    .q .ref { font-size: 8.5pt; color: #767586; margin: 2px 0 0 22px; }

    ol.options { margin: 6px 0 0 22px; padding: 0 0 0 16px; }
    ol.options li { margin-bottom: 2px; }

    .answer {
        margin: 6px 0 0 22px;
        padding: 7px 9px;
        border-left: 3px solid #1e8e5a;
        background: #f2fbf6;
        font-size: 10pt;
    }
    .answer .label {
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #1e8e5a;
        font-weight: bold;
        display: block;
        margin-bottom: 2px;
    }
    .correct { font-weight: bold; }

    .lines { margin: 6px 0 0 22px; }
    .lines div { border-bottom: 1px solid #d5d6e0; height: 17px; }

    table.scheme { width: 100%; border-collapse: collapse; }
    table.scheme th, table.scheme td { border: 1px solid #e0e3e5; padding: 5px 7px; text-align: left; vertical-align: top; }
    table.scheme th { background: #f4f4f8; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.4px; color: #45464f; }
</style>
