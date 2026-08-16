// Vercel Serverless Function — proxies /api/* to the PHP backend.
// Unlike vercel.json "rewrites" (which only reliably forward GET), this
// function forwards ANY method (GET, POST, file uploads) with the raw body.

const BACKEND = process.env.BACKEND_URL || 'https://srinivasan.free.nf/backend/api';

export default async function handler(req, res) {
  try {
    const path = req.url.replace(/^\/api/, '');
    const targetUrl = BACKEND + path;

    // Read the raw request body (works for JSON and multipart/form-data alike)
    const chunks = [];
    for await (const chunk of req) {
      chunks.push(chunk);
    }
    const body = chunks.length ? Buffer.concat(chunks) : undefined;

    // Forward headers, but drop ones that don't make sense cross-host.
    // Force a real browser User-Agent — InfinityFree's anti-bot layer blocks
    // Node's default server-side fetch UA with a JS challenge page.
    const skipHeaders = ['host', 'connection', 'content-length', 'user-agent'];
    const headers = {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    };
    for (const [key, value] of Object.entries(req.headers)) {
      if (skipHeaders.includes(key.toLowerCase())) continue;
      headers[key] = value;
    }

    const upstream = await fetch(targetUrl, {
      method: req.method,
      headers,
      body: ['GET', 'HEAD'].includes(req.method) ? undefined : body,
    });

    res.status(upstream.status);
    upstream.headers.forEach((value, key) => {
      if (!['content-encoding', 'transfer-encoding'].includes(key.toLowerCase())) {
        res.setHeader(key, value);
      }
    });

    const buf = Buffer.from(await upstream.arrayBuffer());
    res.send(buf);
  } catch (err) {
    res.status(502).json({ error: 'Proxy error', detail: String(err) });
  }
}