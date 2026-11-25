<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong — Episteme</title>
    {{-- Self-contained on purpose: a 500 may be caused by the database or the asset build, so no layout, queries, or Vite here. --}}
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #faf8ff; color: #131b2e; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; text-align: center; padding: 1.5rem; }
        main { max-width: 32rem; }
        .code { font-family: ui-monospace, monospace; font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; color: #00668a; font-weight: 600; }
        h1 { font-size: 2rem; margin: .75rem 0; }
        p { color: #3e484f; line-height: 1.6; }
        a { display: inline-block; margin-top: 1.25rem; background: #38bdf8; color: #131b2e; font-weight: 700; padding: .75rem 1.5rem; border-radius: .5rem; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <div class="code">Error 500</div>
        <h1>Something went wrong on our side</h1>
        <p>We hit an unexpected problem and have recorded it. Please try again in a moment.</p>
        <a href="/">Back to home</a>
    </main>
</body>
</html>
