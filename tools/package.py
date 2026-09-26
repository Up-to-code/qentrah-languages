from pathlib import Path
from zipfile import ZipFile,ZIP_DEFLATED
root=Path(__file__).resolve().parent.parent
out=root/'dist';out.mkdir(exist_ok=True)
with ZipFile(out/'qentrah-languages.zip','w',ZIP_DEFLATED) as z:
 for name in ['qentrah-languages.php','uninstall.php','readme.txt','LICENSE','assets']:
  p=root/name
  for f in ([p] if p.is_file() else p.rglob('*')):
   if f.is_file():z.write(f,Path('qentrah-languages')/f.relative_to(root))
print(out/'qentrah-languages.zip')
