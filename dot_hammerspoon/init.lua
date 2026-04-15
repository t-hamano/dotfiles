-- Map the forward delete key (Windows-style Delete) to Cmd+Delete,
-- so it moves the selected item to the Trash in Finder.
hs.hotkey.bind({}, "forwarddelete", function()
  hs.eventtap.keyStroke({"cmd"}, "delete", 0)
end)

-- Toggle input source between US (ABC) and Japanese (Kotoeri) with Shift+Space.
hs.hotkey.bind({"shift"}, "space", function()
  print("hotkey fired! source: " .. hs.keycodes.currentSourceID())
  if hs.keycodes.currentSourceID() == "com.apple.keylayout.ABC" then
    hs.keycodes.currentSourceID("com.apple.inputmethod.Kotoeri.RomajiTyping.Japanese")
  else
    hs.keycodes.currentSourceID("com.apple.keylayout.ABC")
  end
end)
