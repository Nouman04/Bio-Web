<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Past Paper Extractor</title>
    @vite('resources/js/pdf-extractor.js')
    <style>
        body { margin: 0; background: #f4f5f7; color: #1f2328; font: 15px/1.5 system-ui, sans-serif; }
        main { max-width: 900px; margin: 0 auto; padding: 32px 16px 64px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .muted { color: #59636e; margin: 0 0 20px; }
        .upload { display: block; background: #fff; border: 2px dashed #c4c9d0; border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; }
        .upload:hover, .upload:focus-within { border-color: #2f6feb; }
        .upload input { display: block; margin: 12px auto 0; }
        progress { width: 100%; margin-top: 16px; }
        #status { margin: 12px 0 24px; min-height: 1.5em; }
        .question { background: #fff; border: 1px solid #d8dde3; border-radius: 10px; margin-bottom: 24px; overflow: hidden; }
        .question header { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #d8dde3; }
        .question h2 { font-size: 17px; margin: 0; }
        .question button { font: inherit; padding: 4px 12px; border: 1px solid #c4c9d0; border-radius: 6px; background: #fff; cursor: pointer; }
        .question .ql-toolbar.ql-snow, .question .ql-container.ql-snow { border: 0; }
        .question .ql-toolbar.ql-snow { border-bottom: 1px solid #eef0f2; }
        .question .ql-editor { font: 15px/1.6 Georgia, 'Times New Roman', serif; min-height: 200px; }
        .question .ql-editor td { border-color: #8c959f; padding: 6px 8px; }
    </style>
</head>
<body>
<main>
    <h1>Past Paper Extractor</h1>
    <p class="muted">Upload a question paper. Each question opens in its own editor, with its tables and figures. Nothing is uploaded to the server.</p>

    <label class="upload">
        <strong>Choose a question paper (PDF)</strong>
        <input type="file" id="pdf" accept="application/pdf">
    </label>
    <progress id="progress" value="0" max="1" hidden></progress>
    <p id="status" role="status"></p>

    <div id="results"></div>
</main>

<template id="question-template">
    <section class="question">
        <header><h2></h2><button type="button" class="copy">Copy HTML</button></header>
        <div class="editor"></div>
    </section>
</template>
</body>
</html>
