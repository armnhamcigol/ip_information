package main

import (
    "encoding/json"
    "html/template"
    "log"
    "net/http"
    "time"
)

type ConnectionInfo struct {
    ClientIP       string                 `json:"client_ip"`
    ProxyIP        string                 `json:"proxy_ip"`
    ProxyDetected  bool                   `json:"proxy_detected"`
    IsZscaler      bool                   `json:"is_zscaler"`
    Headers        map[string][]string    `json:"headers"`
    ServerTime     string                 `json:"server_time"`
    RequestTime    string                 `json:"request_time"`
    RequestMethod  string                 `json:"request_method"`
    RequestURL     string                 `json:"request_url"`
    HealthCheck    map[string]interface{} `json:"health_check"`
}

const htmlTemplate = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Information</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .info-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .info-card h2 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            color: #666;
            word-break: break-all;
        }
        .proxy-detected {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        .no-proxy {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }
        .json-link {
            text-align: center;
            margin: 20px 0;
        }
        .json-link a {
            color: #3498db;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #3498db;
            border-radius: 5px;
            display: inline-block;
            transition: all 0.3s;
        }
        .json-link a:hover {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 Connection Information</h1>
        
        <div class="proxy-detected {{if .ProxyDetected}}{{else}}no-proxy{{end}}">
            {{if .ProxyDetected}}
                ✅ Proxy/Corporate Network Detected
            {{else}}
                ⚠️ Direct Internet Connection (No Proxy Detected)
            {{end}}
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h2>🖥️ Server Information</h2>
                <div class="info-item">
                    <span class="info-label">Server Time:</span>
                    <span class="info-value">{{.ServerTime}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Request Time:</span>
                    <span class="info-value">{{.RequestTime}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Client IP:</span>
                    <span class="info-value">{{.ClientIP}}</span>
                </div>
                {{if .ProxyIP}}
                <div class="info-item">
                    <span class="info-label">Proxy IP:</span>
                    <span class="info-value">{{.ProxyIP}}</span>
                </div>
                {{end}}
                <div class="info-item">
                    <span class="info-label">Request Method:</span>
                    <span class="info-value">{{.RequestMethod}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Request URL:</span>
                    <span class="info-value">{{.RequestURL}}</span>
                </div>
            </div>

            <div class="info-card">
                <h2>📋 HTTP Headers</h2>
                {{range $key, $values := .Headers}}
                <div class="info-item">
                    <span class="info-label">{{$key}}:</span>
                    <span class="info-value">{{index $values 0}}</span>
                </div>
                {{end}}
            </div>
        </div>

        <div class="json-link">
            <a href="/api" target="_blank">📄 View Raw JSON Data</a>
        </div>

        <div class="footer">
            <p>Connection Info Service • Raspberry Pi • Updated {{.ServerTime}}</p>
        </div>
    </div>
</body>
</html>
`

func getConnectionInfo(r *http.Request) *ConnectionInfo {
    now := time.Now()
    
    clientIP := r.RemoteAddr
    if realIP := r.Header.Get("X-Real-IP"); realIP != "" {
        clientIP = realIP
    }
    
    proxyIP := r.Header.Get("X-Forwarded-For")
    
    // Enhanced proxy detection
    proxyDetected := false
    isZscaler := false
    
    proxyHeaders := []string{
        "X-Forwarded-For",
        "X-Real-IP",
        "X-Forwarded-Proto",
        "X-Forwarded-Host",
        "Via",
        "X-Proxy-Authorization",
        "Forwarded",
    }
    
    for _, header := range proxyHeaders {
        if r.Header.Get(header) != "" {
            proxyDetected = true
            break
        }
    }
    
    // Check for Zscaler-specific headers
    zscalerHeaders := []string{"X-Zscaler-Via", "X-Zscaler-Client-IP", "Z-Forwarded-For"}
    for _, header := range zscalerHeaders {
        if r.Header.Get(header) != "" {
            isZscaler = true
            proxyDetected = true
            break
        }
    }
    
    return &ConnectionInfo{
        ClientIP:      clientIP,
        ProxyIP:       proxyIP,
        ProxyDetected: proxyDetected,
        IsZscaler:     isZscaler,
        Headers:       r.Header,
        ServerTime:    now.Format("2006-01-02 15:04:05 MST"),
        RequestTime:   now.Format(time.RFC3339),
        RequestMethod: r.Method,
        RequestURL:    r.URL.String(),
        HealthCheck: map[string]interface{}{
            "status":    "ok",
            "timestamp": now.Format(time.RFC3339),
        },
    }
}

func htmlHandler(w http.ResponseWriter, r *http.Request) {
    info := getConnectionInfo(r)
    
    tmpl, err := template.New("index").Parse(htmlTemplate)
    if err != nil {
        http.Error(w, "Template error", http.StatusInternalServerError)
        log.Printf("Template parsing error: %v", err)
        return
    }
    
    w.Header().Set("Content-Type", "text/html; charset=utf-8")
    if err := tmpl.Execute(w, info); err != nil {
        log.Printf("Template execution error: %v", err)
    }
}

func apiHandler(w http.ResponseWriter, r *http.Request) {
    info := getConnectionInfo(r)
    
    w.Header().Set("Content-Type", "application/json")
    w.Header().Set("Access-Control-Allow-Origin", "*")
    w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
    w.Header().Set("Access-Control-Allow-Headers", "Content-Type")
    
    if err := json.NewEncoder(w).Encode(info); err != nil {
        log.Printf("JSON encoding error: %v", err)
        http.Error(w, "JSON encoding error", http.StatusInternalServerError)
    }
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
    health := map[string]interface{}{
        "status":    "ok",
        "timestamp": time.Now().Format(time.RFC3339),
        "service":   "connection-info",
    }
    
    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(health)
}

func main() {
    http.HandleFunc("/", htmlHandler)
    http.HandleFunc("/api", apiHandler)
    http.HandleFunc("/health", healthHandler)
    
    log.Println("Server starting on :8080")
    log.Println("  - HTML interface: /")
    log.Println("  - JSON API: /api")
    log.Println("  - Health check: /health")
    
    if err := http.ListenAndServe(":8080", nil); err != nil {
        log.Fatal("Server failed to start:", err)
    }
}

