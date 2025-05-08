import requests
import json

# Webhook URL (n8n webhook)
webhook_url = 'http://10.0.0.3:30109/webhook/b5e53354-f7a6-4b04-8fb9-ce152894409c'

# Test data (keeping it simple for testing)
data = {
    "message": "Test message",
    "name": "Test User",
    "timestamp": "2024-03-21"
}

# Send POST request to n8n webhook
try:
    response = requests.post(
        webhook_url,
        headers={'Content-Type': 'application/json'},
        json=data
    )
    
    # Print response (n8n typically returns a 200 status code)
    print(f"Status Code: {response.status_code}")
    if response.status_code == 200:
        print("Successfully triggered n8n webhook!")
    print(f"Response: {response.text}")
    
except Exception as e:
    print(f"Error occurred: {e}")
