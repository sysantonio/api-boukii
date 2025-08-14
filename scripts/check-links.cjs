const fs = require('fs');
const path = require('path');

function getFiles(dir) {
  return fs.readdirSync(dir).flatMap(name => {
    const full = path.join(dir, name);
    return fs.statSync(full).isDirectory() ? getFiles(full) : [full];
  });
}

function checkLinks(file) {
  const content = fs.readFileSync(file, 'utf8');
  const regex = /\[[^\]]*\]\((?!https?:\/\/)([^)#]+)\)/g;
  let match;
  while ((match = regex.exec(content)) !== null) {
    const link = match[1];
    const resolved = path.resolve(path.dirname(file), link);
    if (!fs.existsSync(resolved)) {
      broken.push({ file, link });
    }
  }
}

const docsDir = path.join(__dirname, '..', 'docs');
const files = getFiles(docsDir).filter(f => f.endsWith('.md'));
const broken = [];
files.forEach(checkLinks);

if (broken.length) {
  console.error('Broken links:');
  broken.forEach(b => console.error(`${b.file} -> ${b.link}`));
  process.exitCode = 1;
} else {
  console.log('No broken links.');
}
