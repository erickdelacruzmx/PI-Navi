from decouple import Config, RepositoryEnv
import os

def test_env_file(file_path, name):
    print(f"\n=== Probando {name} ===")
    print(f"Archivo: {file_path}")
    print(f"Existe: {os.path.exists(file_path)}")
    
    if os.path.exists(file_path):
        print("Contenido:")
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
            for line in lines:
                print(f"  {line.strip()}")
        
        try:
            config = Config(RepositoryEnv(file_path))
            print("\n✅ Archivo cargado correctamente")
            print(f"DB_NAME: {config('DB_NAME')}")
            print(f"DB_USER: {config('DB_USER')}")
            print(f"DB_PASSWORD: {config('DB_PASSWORD')}")
            print(f"DB_HOST: {config('DB_HOST')}")
            print(f"DB_PORT: {config('DB_PORT')}")
            print(f"DEBUG: {config('DEBUG')}")
            print(f"SECRET_KEY: {config('SECRET_KEY')}")
        except Exception as e:
            print(f"❌ Error: {e}")

# Probar ambas ubicaciones
test_env_file(r'C:\NAVI_ENV\.env', "Ruta absoluta C:\NAVI_ENV\.env")
test_env_file('.env', "Ruta local")