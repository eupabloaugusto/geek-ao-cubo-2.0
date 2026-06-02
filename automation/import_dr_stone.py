import sys
import time
import requests
from import_mal_current_season import MALImporterSeason

def import_dr_stone():
    importer = MALImporterSeason()
    
    # Dr. Stone MAL IDs in chronological release order
    dr_stone_ids = [
        38691, # Dr. Stone
        40852, # Stone Wars
        50612, # Ryuusui
        48549, # New World
        55644, # New World Part 2
        57592, # Science Future
        61322, # Science Future Part 2
        62568, # Science Future Part 3
    ]
    
    print("Iniciando importacao da franquia Dr. Stone...")
    for mal_id in dr_stone_ids:
        print(f"Buscando dados para o MAL ID: {mal_id}...")
        try:
            response = requests.get(f"https://api.jikan.moe/v4/anime/{mal_id}/full")
            if response.status_code == 200:
                data = response.json().get('data')
                if data:
                    print(f"Importando: {data.get('title')}")
                    importer.import_anime(data, dry_run=False, update=True)
            else:
                print(f"Erro ao buscar MAL ID {mal_id}: {response.status_code}")
        except Exception as e:
            print(f"Erro na importacao do {mal_id}: {e}")
            
        time.sleep(2) # Respeitar rate limit da Jikan
        
if __name__ == "__main__":
    import_dr_stone()
