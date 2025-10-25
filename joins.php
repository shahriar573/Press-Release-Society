<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOIN Operations - Press Release Council</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease-out;
        }

        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .matrix-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .matrix-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: fadeInUp 0.6s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .matrix-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3);
        }

        .matrix-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .matrix-icon {
            font-size: 2em;
            margin-right: 15px;
        }

        .matrix-title {
            font-size: 1.5em;
            font-weight: bold;
        }

        .matrix-subtitle {
            font-size: 0.9em;
            opacity: 0.8;
            margin-top: 5px;
        }

        .member-row {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #4CAF50;
            transition: all 0.3s ease;
        }

        .member-row:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .member-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.85em;
            opacity: 0.7;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 1.1em;
            font-weight: 500;
        }

        .releases-container {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .releases-header {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .releases-count {
            background: rgba(76, 175, 80, 0.3);
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 10px;
            font-size: 0.85em;
        }

        .release-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }

        .release-item:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .release-title {
            font-weight: 500;
            flex: 1;
        }

        .release-date {
            font-size: 0.85em;
            opacity: 0.7;
            margin-left: 15px;
        }

        .no-releases {
            text-align: center;
            padding: 20px;
            opacity: 0.5;
            font-style: italic;
        }

        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.5em;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        .error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .back-button {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .dimension-indicator {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75em;
            margin-left: 10px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">← Back to Dashboard</a>
        
        <div class="header">
            <h1>🔗 JOIN Operations Matrix</h1>
            <p>Dimensional Analysis: Members × Press Releases</p>
        </div>

        <div id="content" class="matrix-container">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading dimensional data...</p>
            </div>
        </div>
    </div>

    <script>
        async function loadJoinData() {
            try {
                const response = await fetch('api/join.php');
                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }
                const data = await response.json();
                displayMatrix(data);
            } catch (error) {
                document.getElementById('content').innerHTML = `
                    <div class="error">
                        <h2>⚠️ Error Loading Data</h2>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        function displayMatrix(members) {
            const contentDiv = document.getElementById('content');
            
            if (!members || members.length === 0) {
                contentDiv.innerHTML = '<div class="no-releases">No data available</div>';
                return;
            }

            let html = '';
            
            members.forEach((member, index) => {
                const releaseCount = member.PressReleases ? member.PressReleases.length : 0;
                const dimension = `M[${index + 1}] × R[${releaseCount}]`;
                
                html += `
                    <div class="matrix-card" style="animation-delay: ${index * 0.1}s">
                        <div class="matrix-header">
                            <div class="matrix-icon">👤</div>
                            <div>
                                <div class="matrix-title">
                                    ${member.Name}
                                    <span class="dimension-indicator">${dimension}</span>
                                </div>
                                <div class="matrix-subtitle">${member.Designation || 'Member'}</div>
                            </div>
                        </div>
                        
                        <div class="member-info">
                            <div class="info-item">
                                <span class="info-label">Member ID</span>
                                <span class="info-value">#${member.MemberID}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Contact</span>
                                <span class="info-value">${member.ContactInfo || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <div class="releases-container">
                            <div class="releases-header">
                                📄 Press Releases
                                <span class="releases-count">${releaseCount}</span>
                            </div>
                `;
                
                if (releaseCount > 0) {
                    member.PressReleases.forEach(release => {
                        const date = new Date(release.ReleaseDate);
                        const formattedDate = date.toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                        
                        html += `
                            <div class="release-item">
                                <span class="release-title">${release.Title}</span>
                                <span class="release-date">${formattedDate}</span>
                            </div>
                        `;
                    });
                } else {
                    html += '<div class="no-releases">∅ No press releases authored</div>';
                }
                
                html += `
                        </div>
                    </div>
                `;
            });
            
            contentDiv.innerHTML = html;
        }

        // Load data when page loads
        loadJoinData();
    </script>
</body>
</html>
