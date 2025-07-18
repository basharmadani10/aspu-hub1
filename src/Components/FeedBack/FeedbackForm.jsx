// src/components/FeedbackForm.jsx
import React, { useState } from 'react'

export default function FeedbackForm() {
  const [form, setForm] = useState({
    name: '',
    email: '',
    satisfaction: '',
    usability: '',
    features: '',
    bugs: '',
    comments: '',
    recommend: ''
  })
  const [status, setStatus] = useState('') // '', 'sending', 'success', 'error'

  const handleChange = e => {
    const { name, value } = e.target
    setForm(f => ({ ...f, [name]: value }))
  }

  const handleSubmit = async e => {
    e.preventDefault()
    setStatus('sending')
    try {
      // replace this URL with your real endpoint
      const res = await fetch('/api/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form)
      })
      if (!res.ok) throw new Error(res.statusText)
      setStatus('success')
      setForm({
        name: '',
        email: '',
        satisfaction: '',
        usability: '',
        features: '',
        bugs: '',
        comments: '',
        recommend: ''
      })
    } catch (err) {
      console.error(err)
      setStatus('error')
    }
  }

  return (
    <div className="max-w-xl mx-auto p-6 bg-white shadow-md rounded-lg">
      <h2 className="text-2xl font-semibold mb-4">We Value Your Feedback</h2>
      <form onSubmit={handleSubmit} className="space-y-4 text-gray-800">
        {/* Name & Email */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <input
            name="name"
            value={form.name}
            onChange={handleChange}
            placeholder="Your Name (optional)"
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          <input
            name="email"
            type="email"
            value={form.email}
            onChange={handleChange}
            placeholder="Your Email (optional)"
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        {/* Satisfaction */}
        <div>
          <label className="block mb-1 font-medium">
            How satisfied are you overall?
          </label>
          <select
            name="satisfaction"
            value={form.satisfaction}
            onChange={handleChange}
            required
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">Select…</option>
            <option>😃 Very satisfied</option>
            <option>🙂 Satisfied</option>
            <option>😐 Neutral</option>
            <option>🙁 Unsatisfied</option>
            <option>😠 Very unsatisfied</option>
          </select>
        </div>

        {/* Usability */}
        <div>
          <label className="block mb-1 font-medium">Ease of use</label>
          <select
            name="usability"
            value={form.usability}
            onChange={handleChange}
            required
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">Select…</option>
            <option>★★★★★</option>
            <option>★★★★☆</option>
            <option>★★★☆☆</option>
            <option>★★☆☆☆</option>
            <option>★☆☆☆☆</option>
          </select>
        </div>

        {/* Features */}
        <div>
          <label className="block mb-1 font-medium">
            Which feature did you like most?
          </label>
          <textarea
            name="features"
            value={form.features}
            onChange={handleChange}
            rows="2"
            placeholder="E.g., the new dashboard, search, etc."
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
          />
        </div>

        {/* Bugs */}
        <div>
          <label className="block mb-1 font-medium">
            Any bugs or issues?
          </label>
          <textarea
            name="bugs"
            value={form.bugs}
            onChange={handleChange}
            rows="2"
            placeholder="Let us know if you saw any errors."
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
          />
        </div>

        {/* Comments */}
        <div>
          <label className="block mb-1 font-medium">
            Additional comments
          </label>
          <textarea
            name="comments"
            value={form.comments}
            onChange={handleChange}
            rows="3"
            placeholder="Anything else on your mind?"
            className="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
          />
        </div>

        {/* Recommend */}
        <div>
          <label className="block mb-1 font-medium">
            Would you recommend this to others?
          </label>
          <div className="flex items-center space-x-4">
            <label className="flex items-center">
              <input
                type="radio"
                name="recommend"
                value="Yes"
                checked={form.recommend === 'Yes'}
                onChange={handleChange}
                className="mr-2"
                required
              />
              Yes
            </label>
            <label className="flex items-center">
              <input
                type="radio"
                name="recommend"
                value="No"
                checked={form.recommend === 'No'}
                onChange={handleChange}
                className="mr-2"
              />
              No
            </label>
          </div>
        </div>

        {/* Submit & Status */}
        <div className="pt-4">
          <button
            type="submit"
            disabled={status === 'sending'}
            className={`w-full py-2 px-4 rounded font-medium text-white ${
              status === 'sending'
                ? 'bg-gray-400'
                : 'bg-indigo-600 hover:bg-indigo-700'
            } transition`}
          >
            {status === 'sending' ? 'Sending…' : 'Submit Feedback'}
          </button>

          {status === 'success' && (
            <p className="mt-2 text-green-600">Thanks for your feedback!</p>
          )}
          {status === 'error' && (
            <p className="mt-2 text-red-600">
              Oops, something went wrong. Please try again.
            </p>
          )}
        </div>
      </form>
    </div>
  )
}
