import os
import requests
import json
from dotenv import load_dotenv

load_dotenv()
WP_URL = os.getenv("WP_BASE_URL", "http://geekaocubocom.local")

print(f"Executando limpeza em {WP_URL}/wp-json/geek/v1/clean...")
try:
    resp = requests.post(f"{WP_URL}/wp-json/geek/v1/clean")
    if resp.status_code == 200:
        print(json.dumps(resp.json(), indent=2))
    else:
        print(f"Erro: {resp.text}")
except Exception as e:
    print(f"Exception: {e}")
