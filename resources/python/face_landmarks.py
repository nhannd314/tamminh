"""
Nhận diện khuôn mặt, cắt ảnh và đo Tam đình bằng MediaPipe Face Mesh.

Usage: python face_landmarks.py <ảnh gốc> <ảnh cắt đầu ra>

In ra một dòng JSON:
  {"found": false}
  {"found": true,
   "box": [x, y, w, h],                    # vùng cắt, % theo ảnh gốc
   "lines": [chân tóc, chân mày, chân mũi, cằm],  # % theo chiều cao ảnh cắt
   "ratios": [thượng, trung, hạ],          # % mỗi đình, tổng = 100
   "features": {"long_may": [x, y, w, h], "mat": …, "tai": …, "mui": …, "mieng": …}}
                                           # vùng từng ngũ quan: [x, y, w, h] % theo ảnh cắt + tỉ lệ rộng/cao

Bốn đường chia mặt:
  - Chân tóc: quét từ đỉnh lưới mặt (điểm 10) lên trên tới khi hết màu da trán.
  - Chân mày: trung bình các điểm trên của hai cung mày.
  - Chân mũi: điểm dưới mũi (subnasale, điểm 2).
  - Cằm: điểm thấp nhất của cằm (điểm 152).
"""

import json
import sys

import cv2
import mediapipe as mp
import numpy as np

FOREHEAD_TOP = 10
FOREHEAD_CENTER = 151
BROWS = [105, 66, 334, 296]
NOSE_BASE = 2
CHIN = 152
FACE_SIDES = [234, 454]

# Các điểm lưới mặt bao quanh từng ngũ quan (tai không có trong lưới nên suy ra từ mép mặt).
FEATURE_POINTS = {
    "long_may": [70, 63, 105, 66, 107, 46, 53, 52, 65, 55, 300, 293, 334, 296, 336, 276, 283, 282, 295, 285],
    "mat": [33, 246, 161, 160, 159, 158, 157, 173, 133, 155, 154, 153, 145, 144, 163, 7,
            362, 398, 384, 385, 386, 387, 388, 466, 263, 249, 390, 373, 374, 380, 381, 382],
    "mui": [168, 6, 197, 195, 5, 4, 1, 2, 98, 327, 129, 358, 102, 331, 64, 294],
    "mieng": [61, 291, 0, 17, 13, 14, 37, 267, 84, 314, 185, 409, 146, 375, 40, 270, 91, 321],
}
# Tỉ lệ rộng/cao của khung cắt từng bộ phận (lông mày và mắt là dải ngang, tai là dải đứng).
FEATURE_ASPECTS = {"long_may": 2.2, "mat": 2.2, "tai": 0.75, "mui": 1.1, "mieng": 1.6}
FEATURE_PADDING = 0.18


def read_image(path):
    # cv2.imread không đọc được đường dẫn Unicode trên Windows.
    data = np.fromfile(path, dtype=np.uint8)
    return cv2.imdecode(data, cv2.IMREAD_COLOR)


def find_hairline(image, landmarks, height, width):
    """Quét lên từ đỉnh lưới mặt, dừng khi dải giữa trán không còn màu da."""
    top_y = landmarks[FOREHEAD_TOP][1]
    center_x = int(landmarks[FOREHEAD_CENTER][0])
    face_width = landmarks[FACE_SIDES[1]][0] - landmarks[FACE_SIDES[0]][0]
    half_strip = max(3, int(face_width * 0.12))

    ycrcb = cv2.cvtColor(image, cv2.COLOR_BGR2YCrCb).astype(np.float32)
    sample_y = int(landmarks[FOREHEAD_CENTER][1])
    patch = ycrcb[max(0, sample_y - half_strip):sample_y + half_strip, max(0, center_x - half_strip):center_x + half_strip]
    skin = patch.reshape(-1, 3).mean(axis=0)

    limit = max(0, int(top_y - face_width * 0.45))
    misses = 0

    for y in range(int(top_y), limit, -1):
        row = ycrcb[y, max(0, center_x - half_strip):min(width, center_x + half_strip)]
        distance = np.abs(row[:, 1:] - skin[1:]).sum(axis=1) + np.abs(row[:, 0] - skin[0]) * 0.35
        if (distance < 28).mean() < 0.5:
            misses += 1
            if misses >= 3:
                return float(y + misses)
        else:
            misses = 0

    return float(top_y)


def feature_box(points, aspect, crop_left, crop_top, crop_width, crop_height, min_width):
    """Hộp bao các điểm, nới lề và ép về tỉ lệ `aspect`, đổi sang % theo ảnh cắt (kèm tỉ lệ ở cuối)."""
    xs = [x for x, _ in points]
    ys = [y for _, y in points]
    box_width = max(max(xs) - min(xs), min_width) * (1 + FEATURE_PADDING * 2)
    box_height = (max(ys) - min(ys)) * (1 + FEATURE_PADDING * 2)
    box_width = max(box_width, box_height * aspect)
    box_height = box_width / aspect

    if box_width > crop_width:
        box_width = crop_width
        box_height = box_width / aspect
    if box_height > crop_height:
        box_height = crop_height
        box_width = box_height * aspect

    center_x = (max(xs) + min(xs)) / 2
    center_y = (max(ys) + min(ys)) / 2
    left = float(np.clip(center_x - box_width / 2, crop_left, crop_left + crop_width - box_width))
    top = float(np.clip(center_y - box_height / 2, crop_top, crop_top + crop_height - box_height))

    return [
        round((left - crop_left) / crop_width * 100, 2),
        round((top - crop_top) / crop_height * 100, 2),
        round(box_width / crop_width * 100, 2),
        round(box_height / crop_height * 100, 2),
        aspect,
    ]


def main():
    source, output = sys.argv[1], sys.argv[2]
    image = read_image(source)

    if image is None:
        raise SystemExit("Không đọc được ảnh: " + source)

    height, width = image.shape[:2]

    with mp.solutions.face_mesh.FaceMesh(static_image_mode=True, max_num_faces=1, min_detection_confidence=0.5) as mesh:
        result = mesh.process(cv2.cvtColor(image, cv2.COLOR_BGR2RGB))

    if not result.multi_face_landmarks:
        print(json.dumps({"found": False}))
        return

    landmarks = [(point.x * width, point.y * height) for point in result.multi_face_landmarks[0].landmark]

    hairline = find_hairline(image, landmarks, height, width)
    brow = float(np.mean([landmarks[i][1] for i in BROWS]))
    nose = landmarks[NOSE_BASE][1]
    chin = landmarks[CHIN][1]

    # Khung cắt gần vuông (tỉ lệ 250:255 như thiết kế), chừa lề quanh mặt.
    face_height = chin - hairline
    face_width = landmarks[FACE_SIDES[1]][0] - landmarks[FACE_SIDES[0]][0]
    crop_height = max(face_height * 1.35, face_width * 1.4 * 255 / 250)
    crop_width = crop_height * 250 / 255
    center_x = (landmarks[FACE_SIDES[0]][0] + landmarks[FACE_SIDES[1]][0]) / 2
    center_y = (hairline + chin) / 2

    crop_width = min(crop_width, width)
    crop_height = min(crop_height, height)
    left = int(np.clip(center_x - crop_width / 2, 0, width - crop_width))
    top = int(np.clip(center_y - crop_height / 2, 0, height - crop_height))
    right, bottom = int(left + crop_width), int(top + crop_height)

    ok, encoded = cv2.imencode(".jpg", image[top:bottom, left:right], [cv2.IMWRITE_JPEG_QUALITY, 88])
    if not ok:
        raise SystemExit("Không ghi được ảnh cắt")
    encoded.tofile(output)

    crop_h = bottom - top
    lines = [round((y - top) / crop_h * 100, 2) for y in (hairline, brow, nose, chin)]

    parts = [brow - hairline, nose - brow, chin - nose]
    total = sum(parts)
    ratios = [round(part / total * 100) for part in parts]
    ratios[1] = 100 - ratios[0] - ratios[2]

    face_w = landmarks[FACE_SIDES[1]][0] - landmarks[FACE_SIDES[0]][0]
    crop_w = right - left
    features = {}
    for name, indexes in FEATURE_POINTS.items():
        features[name] = feature_box([landmarks[i] for i in indexes], FEATURE_ASPECTS[name], left, top, crop_w, crop_h, face_w * 0.3)

    # Tai: lưới mặt dừng ở mép trước tai, nên lấy dải ngay bên ngoài điểm 234 (tai bên trái ảnh), cao từ chân mày tới chân mũi.
    ear_x = landmarks[FACE_SIDES[0]][0]
    ear_points = [(ear_x - face_w * 0.22, brow), (ear_x - face_w * 0.02, nose)]
    features["tai"] = feature_box(ear_points, FEATURE_ASPECTS["tai"], left, top, crop_w, crop_h, face_w * 0.18)

    print(json.dumps({
        "found": True,
        "box": [round(left / width * 100, 2), round(top / height * 100, 2), round((right - left) / width * 100, 2), round(crop_h / height * 100, 2)],
        "lines": lines,
        "ratios": ratios,
        "features": {name: features[name] for name in ("long_may", "mat", "tai", "mui", "mieng")},
    }))


if __name__ == "__main__":
    main()
