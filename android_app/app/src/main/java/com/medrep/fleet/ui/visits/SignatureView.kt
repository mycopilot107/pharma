package com.medrep.fleet.ui.visits

import android.content.Context
import android.graphics.*
import android.util.AttributeSet
import android.util.Base64
import android.view.MotionEvent
import android.view.View
import java.io.ByteArrayOutputStream

class SignatureView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyle: Int = 0
) : View(context, attrs, defStyle) {

    private val paths  = mutableListOf<Path>()
    private var current = Path()

    private val paint = Paint().apply {
        color       = Color.BLACK
        strokeWidth = 5f
        style       = Paint.Style.STROKE
        isAntiAlias = true
        strokeCap   = Paint.Cap.ROUND
        strokeJoin  = Paint.Join.ROUND
    }

    private val bgPaint = Paint().apply {
        color = Color.parseColor("#FFFDE7")
        style = Paint.Style.FILL
    }

    init {
        setWillNotDraw(false)
    }

    override fun onDraw(canvas: Canvas) {
        canvas.drawRect(0f, 0f, width.toFloat(), height.toFloat(), bgPaint)
        paths.forEach { canvas.drawPath(it, paint) }
        canvas.drawPath(current, paint)
    }

    override fun onTouchEvent(event: MotionEvent): Boolean {
        when (event.action) {
            MotionEvent.ACTION_DOWN -> {
                current = Path()
                current.moveTo(event.x, event.y)
                paths.add(current)
            }
            MotionEvent.ACTION_MOVE -> current.lineTo(event.x, event.y)
            MotionEvent.ACTION_UP   -> current.lineTo(event.x, event.y)
        }
        invalidate()
        return true
    }

    fun clear() {
        paths.clear()
        current = Path()
        invalidate()
    }

    fun isEmpty() = paths.isEmpty()

    fun toBase64(): String? {
        if (isEmpty() || width <= 0 || height <= 0) return null
        val bmp = Bitmap.createBitmap(width, height, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        canvas.drawColor(Color.WHITE)
        paths.forEach { canvas.drawPath(it, paint) }
        val stream = ByteArrayOutputStream()
        bmp.compress(Bitmap.CompressFormat.PNG, 90, stream)
        bmp.recycle()
        return Base64.encodeToString(stream.toByteArray(), Base64.NO_WRAP)
    }
}
