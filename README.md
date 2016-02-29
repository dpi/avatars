Provides a selection of generated avatars for users.

Copyright (C) 2015 Daniel Phin (@dpi)

[![Build Status](https://travis-ci.org/dpi/avatars.svg?branch=master)](https://travis-ci.org/dpi/avatars)

# License

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

# Configuration

 1. Install and enable Avatar Kit
 2. Install and enable optional integration modules: Gravatar, Robohash etc.
 3. Go to 'Avatar Kit settings' (Administration » Configuration » People)
 4. Click 'Add avatar generator' local action button.
 5. Click the generator you wish to use. Click 'Save'. Configure if required.
 6. Repeat 4-5 until you have added all avatar generators you wish to use.
 7. Return to 'Avatar Kit settings', click 'Enable' checkbox adjacent to each
    generator you wish to enable. Re-order the generators based on priority you
    want to try for each user. Higher generators are tried first.
 8. Click 'Save configuration' button.
 9. Go to 'Permissions' page (Administration » Configuration » People).
 10. Scroll the page until you find 'Avatar Kit' category. Click checkboxes
    under each role you wish to enable each generator.
 11. Click 'Save permissions' button.

# Problems?

Avatar Kit avatars are cached heavily. Normally your site cache will expire over
time, and avatars will appear on their own. If for some reason you wish to see
an avatar immediately, you can clear your site cache (Administration » 
Configuration » Development).
