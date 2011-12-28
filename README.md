# TracBoard

TracBoard is a whiteboard-style Agile planning tool built on top of [Trac](http://trac.edgewall.org/ "Trac website")!

### What it does

TracBoard displays a large-format, card-like view of development tasks within milestones.  It allows you to move
them around easily as you and your team move your way through your sprints.  It works for planning activities as
well as for monitoring mid-sprint burndowns and swimlanes.

### How it does it

TracBoard relies entirely on data stored within a Trac setup.  It assumes you use Trac and have a workflow that 
generally supports agile sprints. It hooks up to Trac via the XML RPC API using the user's authentication, and
does not duplicate any data at all -- there is no additional data storage for TracBoard.

Think of TracBoard as simply another view onto the core Trac system; one that makes some assumptions about the 
structure of your Trac data, but gives you a much more intuitive whiteboard-style interface.

TracBoard is PHP and should run on a fairly vanilla configuration.

### Why you might care

TracBoard came out of a need for a whiteboard-style visualization of our development milestones, but a 
desire to avoid yet another tool when Trac met so many of our detailed tracking needs.  We absolutely did not
want to have to manage another database of items to be worked on, when all of our detailed defects and other work
were managed effectively in Trac.  We wanted one database, but with a more useful view than Trac was providing.  

If you find yourself struggling with the need for a convenient board-style visualization and planning tool, but not
wanting to deal with duplicate data or with using one system for defects and another for feature requests, take a 
look at this.

## A Quick Tour

TracBoard has three main views: 
 * Roadmap
 * Sprint
 * Swimlane

### Roadmap

The _Roadmap_ view is useful for planning development sprints. It lets you view any number of milestones in parallel,
including the special _Backlog_ milestone, and move items between them. Defects, stories, and features are represented
as cards, which can be grouped and colored according to numerous different schemes (priority, owner, severity, and more).

![The Roadmap view](http://farm8.staticflickr.com/7153/6585146011_abb2164653.jpg "An example roadmap view")

[see larger image](http://www.flickr.com/photos/sptm/6585146011)

### Sprint

The _Sprint_ view is useful for moving items around within the context of an individual development sprint.  Its
easy to see the status of various development items within the current sprint. Items are represented by cards as they
are in the roadmap view, and have the same types of options.

![The Sprint view](http://farm8.staticflickr.com/7009/6585266711_f7238d7270.jpg "An example sprint view")

[see larger image](http://www.flickr.com/photos/sptm/6585266711)

### Swimlane

Finally, the _Swimlane_ view is another view on an in-progress development sprint, oriented in terms of per-story swimlanes.
A swimlane is a row of activity around a specific story, making it easy to see the state of all of the items that are
related to that story.  The same coloring options apply.

![The Swimlane view](http://farm8.staticflickr.com/7028/6585266889_827ee46f68.jpg "An example swimlane view")

[see larger image](http://www.flickr.com/photos/sptm/6585266889)

## Requirements and Setup

*Caveat Emptor:* TracBoard is an internal-use tool and us not fully vetted for use in Trac setups that are not 
absolutely identical to ours.  That said, we're happy to help you try to get things up and running if you'd like to 
try.

TracBoard is PHP, and runnable from within a source distribution.  Edit `src/webroot/config.inc.php` with your own
configuration and go from there.

### About Trac Workflow Requirements

TracBoard makes several assumptions about the underlying structure of the Trac system, which may not map to your own
installation and workflow.  This is the area in which TracBoard currently lacks the most -- it is relatively inflexible
when it comes to Trac workflow requirements.

The Trac configuration TracBoard assumes is, in brief:
 * development sprints correspond to _milestones_
 * milestones are named in the form YYYYMMDD_*, with special-meaning _TBD_ and _Backlog_ milestones
 * tickets are organized into _Pipelines_, implemented as custom fields, representing streams of work (teams, in our case)
 * ticket types are _story_, _defect_, _feature_, and _task_, where story tickets have special meaning 
   and are assumed to have dependent tickets representing the elements of work that need to be completed for the story
  
## FIXME
 
### Feature Requests

* move subtickets when moving pris on parent ticket (probably some more general version of this is possible as well) 

* save state for which coloring groups are on/off

* lighter fonts when tickets have dark color

* make story/ticket id copyable

* coloring:
  * "enable all" and "disable all" for coloring
  * hide rows

* in swimlane, make stories have their own appearance

* in sprint view, be able to drag between columns to change status

* in swimlane view, ability to add new top-level stories

* in swimlane view, allow tickets to be dragged into other swimlanes (meaning a re-parent)

* add wontfix quick-action in edit view

* when doing drag and drop:
  * should be able to drop into groupings (not just columns)
  * should show up right away in the new location, not wait for a column refresh
  * shouldn't be able to drop into the region where it already is

* be able to limit/filter views to only a certain component, for individual planning (use tracboard itself as an example, or maybe "data request").  maybe "any" field as well, some kind of regex or something

* Projector stylesheet

* More device-specific/optimized display on ipad

* make colors for color-by-who static, not dynamic (so people always get the same color)

* notification to other connected clients when items are moved

### Possible Bugs

* cards should wrap in rows, aligned to top (in Chrome, they wrap at bottom)

* make sure counts in group and column headers update when dragging and hiding (inc and dec appropriately)





