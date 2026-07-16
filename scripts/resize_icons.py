import os
from pathlib import Path
from PIL import Image

# Paths
base_dir = Path(r'd:/lomba/GeoCrime-web')
icon_src = base_dir / 'public' / 'icons' / 'icon_app.png'

if not icon_src.is_file():
    raise FileNotFoundError(f"Source icon not found: {icon_src}")

# Define target sizes and destinations
targets = [
    (96, base_dir / 'icons' / 'icon-96.png'),
    (192, base_dir / 'icons' / 'icon-192.png'),
    (512, base_dir / 'icons' / 'icon-512.png'),
    (512, base_dir / 'icons' / 'maskable-512.png'),
    (96, base_dir / 'favicon-96.png'),
    (192, base_dir / 'favicon-192.png'),
    (512, base_dir / 'favicon-512.png'),
    (180, base_dir / 'apple-touch-icon.png'),
    # Public directory targets
    (96, base_dir / 'public' / 'icons' / 'icon-96.png'),
    (192, base_dir / 'public' / 'icons' / 'icon-192.png'),
    (512, base_dir / 'public' / 'icons' / 'icon-512.png'),
    (512, base_dir / 'public' / 'icons' / 'maskable-512.png'),
    (96, base_dir / 'public' / 'favicon-96.png'),
    (192, base_dir / 'public' / 'favicon-192.png'),
    (512, base_dir / 'public' / 'favicon-512.png'),
    (180, base_dir / 'public' / 'apple-touch-icon.png'),
]

with Image.open(icon_src) as im:
    for size, dest in targets:
        resized = im.resize((size, size), Image.LANCZOS)
        # Ensure parent directory exists
        dest.parent.mkdir(parents=True, exist_ok=True)
        resized.save(dest, format='PNG')
        print(f"Saved {size}x{size} icon to {dest}")
