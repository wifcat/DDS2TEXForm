# written by ATERIS
# 2025-07-01
# Backend DDS -> TEX with in-memory I/O (no disk residue)

from flask import Flask, request, send_file, jsonify
import os
import subprocess
import tempfile
import shutil
import io

app = Flask(__name__)

MENTAH = "mentahan.tex"  # Harus tersedia di direktori yang sama


@app.route("/", methods=["GET"])
def home():
    return """
    <h1>API DDS to TEX converter</h1>
    <p>Kirim POST dengan file DDS ke endpoint ini untuk konversi.</p>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="ddsfile" accept=".dds" required>
      <button type="submit">Konversi</button>
    </form>
    """


@app.route("/", methods=["POST"])
def convert():
    if 'ddsfile' not in request.files:
        return jsonify({"error": "No DDS file provided"}), 400

    if not os.path.isfile(MENTAH):
        return jsonify({"error": "Template 'mentahan.tex' not found"}), 500

    dds_file = request.files['ddsfile']
    base = os.path.splitext(dds_file.filename)[0]

    # Gunakan temporary file untuk DDS dan TEX
    temp_dds = tempfile.NamedTemporaryFile(suffix=".dds", delete=False)
    temp_tex = tempfile.NamedTemporaryFile(suffix=".tex", delete=False)

    try:
        # Simpan DDS upload ke temp file
        temp_dds.write(dds_file.read())
        temp_dds.flush()
        temp_dds.close()

        # Copy mentahan.tex ke temp_tex
        shutil.copy(MENTAH, temp_tex.name)
        temp_tex.close()

        # Jalankan script konversi
        cmd = [
            "python3", "dds2tex.py", "-i", temp_dds.name, "-o", temp_tex.name,
            "-c"
        ]
        proc = subprocess.run(cmd, capture_output=True)

        if proc.returncode != 0:
            return jsonify({"error": proc.stderr.decode()}), 500

        # Baca hasil TEX
        with open(temp_tex.name, "rb") as f:
            tex_data = f.read()

    finally:
        os.unlink(temp_dds.name)
        os.unlink(temp_tex.name)

    return send_file(io.BytesIO(tex_data),
                     as_attachment=True,
                     download_name=base + ".tex",
                     mimetype="application/octet-stream")


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port)
