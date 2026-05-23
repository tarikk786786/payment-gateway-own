    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            /*background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);*/
            color: #e0e0e0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        header {
            text-align: center;
            margin-bottom: 5px;
        }

        header h1 {
            font-size: 2rem;
            color: blue;
            text-shadow: 0 0 10px rgba(0, 221, 235, 0.5);
            margin-bottom: 5px;
        }

        .container {
            max-width: 800px;
            width: 100%;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .tip {
            background: purple;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tip:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 221, 235, 0.2);
        }

        .tip h2 {
            font-size: 1rem;
            color: #00ddeb;
            margin-bottom: 5px;
        }

        .tip p {
            font-size: 0.75rem;
            color: White;
        }

        .tip code {
            background: #2a2a3e;
            padding: 2px 6px;
            border-radius: 4px;
            color: #ff6f61;
            font-family: 'Fira Code', monospace;
        }


        @media (max-width: 600px) {
            header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 20px;
            }

            .tip h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
    <header>
        <h1>Merchant Connection Guide</h1>
    </header>
    
    